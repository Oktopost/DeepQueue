<?php
namespace DeepQueue\Plugins\Connectors\RedisConnector\DAO;


use DeepQueue\Utils\TimeGenerator;
use DeepQueue\Base\Config\IRedisConfig;
use DeepQueue\Base\Plugins\ConnectorElements\IQueueManagerDAO;
use DeepQueue\Plugins\Connectors\RedisConnector\Base\IRedisQueueDAO;
use DeepQueue\Plugins\Connectors\RedisConnector\Helper\RedisNameBuilder;

use Predis\Client;
use Predis\Transaction\MultiExec;


/**
 * @autoload
 */
class RedisQueueDAO implements IRedisQueueDAO, IQueueManagerDAO
{
	/** @var Client	*/
	private $client;
	
	
	private function prepareNow(string $queueName, MultiExec $transaction, $payloads): void
	{
		$transaction->rpush(RedisNameBuilder::getNowKey($queueName), $payloads);
	}
	
	private function preparePayloads(string $queueName, MultiExec $transaction, $payloads): void 
	{
		$transaction->hmset(RedisNameBuilder::getPayloadsKey($queueName), $payloads['keyValue']);
	}
	
	private function prepareDelayed(string $queueName, MultiExec $transaction, $payloads): void
	{
		$delayed = [];
		
		foreach ($payloads as $key => $delay)
		{
			$delayed[$key] = TimeGenerator::getMs($delay);
		}
		
		$transaction->zadd(RedisNameBuilder::getDelayedKey($queueName), 'NX', $delayed);
	}
	
	private function setupEnqueueTransaction(string $queueName, $payloads): MultiExec
	{
		$transaction = $this->client->transaction();

		if (isset($payloads['keyValue']))
		{
			$this->preparePayloads($queueName, $transaction, $payloads);
		}
		
		if (isset($payloads['now']))
		{
			$this->prepareNow($queueName, $transaction, $payloads['now']);
		}

		if (isset($payloads['delayed']))
		{
			$this->prepareDelayed($queueName, $transaction, $payloads['delayed']);
		}

		return $transaction;
	}
	
	private function addZeroKeyIfEmpty(string $queueName): void
	{
		if ($this->client->llen(RedisNameBuilder::getNowKey($queueName)) == 0)
		{
			$this->client->rpush(RedisNameBuilder::getNowKey($queueName), [RedisNameBuilder::getZeroKey()]);
		}
	}
	
	private function getKeys(string $queueName, int $count, ?string $initialKey = null): array 
	{
		$dequeuingKeys = [];
		
		if ($initialKey)
		{
			$dequeuingKeys[] = $initialKey;
		}
		
		if ($count <= 0)
		{
			return $dequeuingKeys;
		}

		$transaction = $this->client->transaction();
		
		$transaction->lrange(RedisNameBuilder::getNowKey($queueName), 0, $count - 1);
		$transaction->ltrim(RedisNameBuilder::getNowKey($queueName), $count, -1);
		
		$response = $transaction->execute();

		$dequeuingKeys = array_merge($dequeuingKeys, $response[0]);
		
		return array_unique($dequeuingKeys);
	}
	
	private function getPayloads(string $queueName, array $keys): array
	{
		if (!$keys)
			return [];
		
		$transaction = $this->client->transaction();
		
		$transaction->hmget(RedisNameBuilder::getPayloadsKey($queueName), $keys);
		$transaction->hdel(RedisNameBuilder::getPayloadsKey($queueName), $keys);
		
		$response = $transaction->execute();
		
		$payloads = $response[0];

		return array_filter(array_combine($keys, $payloads));
	}
	
	
	public function initClient(IRedisConfig $config): void
	{
		$this->client = new Client($config->getParameters(), $config->getOptions());
	}

	public function enqueue(string $queueName, array $payloads): array
	{
		if (!$payloads)
			return [];
		
		$transaction = $this->setupEnqueueTransaction($queueName, $payloads);
		
		$transaction->execute();
		
		$now_keys = isset($payloads['now']) ? $payloads['now'] : [];
		$delayed_keys = isset($payloads['delayed']) ? array_keys($payloads['delayed']) : [];
		
		if (!$now_keys && $delayed_keys)
		{
			$this->addZeroKeyIfEmpty($queueName);
		}
		
		return array_merge($now_keys, $delayed_keys);
	}
	
	public function dequeueInitialKey($queueName, $waitSeconds): ?string
	{
		$waitSeconds = $waitSeconds == 0 ? -1 : $waitSeconds;
		
		if ($waitSeconds > 0)
		{
			$key = $this->client->blpop([RedisNameBuilder::getNowKey($queueName)], $waitSeconds);
			$key = $key[1];
		}
		else
		{
			$key = $this->client->lpop(RedisNameBuilder::getNowKey($queueName));
		}
		
		if (!$key)
			return null;
		
		return $key;
	}
	
	public function dequeueAll(string $queueName, int $count, ?string $initialKey = null): array
	{
		$keys = $this->getKeys($queueName, $count, $initialKey);

		return $this->getPayloads($queueName, $keys);
	}
	
	public function popDelayed(string $queueName, ?int $time = 0): void
	{
		if (!$time)
		{
			$time = TimeGenerator::getMs();
		}
		
		$delayed = $this->client->zrangebyscore(RedisNameBuilder::getDelayedKey($queueName), 
			0, $time);

		if (!$delayed)
		{
			return;
		}
		
		$transaction = $this->client->transaction();
		
		$this->prepareNow($queueName, $transaction, $delayed);
		
		$transaction->zrem(RedisNameBuilder::getDelayedKey($queueName), $delayed);
		
		$transaction->execute();
	}
	
	public function getFirstDelayed(string $queueName): array 
	{
		$result = $this->client->zrange(RedisNameBuilder::getDelayedKey($queueName), 
				0, 0, 'WITHSCORES');
		
		return $result ?: [];
	}
	
	public function countEnqueued(string $queueName): int
	{
		return $this->client->hlen(RedisNameBuilder::getPayloadsKey($queueName));
	}

	public function countDelayed(string $queueName): int
	{
		return $this->client->zcard(RedisNameBuilder::getDelayedKey($queueName));
	}

	public function clearQueue(string $queueName): void
	{
		$transaction = $this->client->transaction();
		
		$transaction->del([
			RedisNameBuilder::getDelayedKey($queueName),
			RedisNameBuilder::getNowKey($queueName),
			RedisNameBuilder::getPayloadsKey($queueName)
		]);
		
		$transaction->execute();
	}

	public function countNotDelayed(string $queueName): int
	{
		$size = $this->client->llen(RedisNameBuilder::getNowKey($queueName));
		
		if (!$size)
		{
			return 0;
		}
		
		$firstKey = $this->client->lrange(RedisNameBuilder::getNowKey($queueName), 0, 0);
		
		return (isset($firstKey[0]) && $firstKey[0] == RedisNameBuilder::getZeroKey()) ? $size - 1 : $size;
	}

	public function countDelayedReadyToDequeue(string $queueName): int
	{
		return $this->client
			->zcount(RedisNameBuilder::getDelayedKey($queueName),  0, TimeGenerator::getMs());
	}

	public function getDelayedElementByIndex(string $queueName, int $index): array
	{
		return $this->client->zrange(RedisNameBuilder::getDelayedKey($queueName), 
				$index, $index, ['withscores' => true]);
	}

	public function flushDelayed(string $queueName): void
	{
		$this->popDelayed($queueName, PHP_INT_MAX);
	}
}