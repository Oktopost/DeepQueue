<?php
namespace DeepQueue\Plugins\RedisConnector\DAO;


use DeepQueue\Utils\TimeGenerator;
use DeepQueue\Base\Config\IRedisConfig;
use DeepQueue\Plugins\RedisConnector\Helper\BucketNameBuilder;
use DeepQueue\Plugins\RedisConnector\Base\IRedisQueueDAO;

use Predis\Client;
use Predis\Pipeline\Pipeline;


class RedisQueueDAO implements IRedisQueueDAO
{
	private const ZEROKEY = '000-000-000';
	
	
	/** @var Client	*/
	private $client;
	
	
	private function prepareNow(string $queueName, Pipeline $pipeline, $payloads): void
	{
		$pipeline->rpush(BucketNameBuilder::getNowKey($queueName), $payloads);
	}
	
	private function preparePayloads(string $queueName, Pipeline $pipeline, $payloads): void 
	{
		$pipeline->hmset(BucketNameBuilder::getPayloadsKey($queueName), $payloads['keyValue']);
	}
	
	private function prepareDelayed(string $queueName, Pipeline $pipeline, $payloads): void
	{
		$delayed = [];
		
		foreach ($payloads as $key => $delay)
		{
			$delayed[$key] = TimeGenerator::getMs($delay);
		}
		
		$pipeline->zadd(BucketNameBuilder::getDelayedKey($queueName), $delayed);
	}
	
	private function setupEnqueuePipeline(string $queueName, $payloads): Pipeline
	{
		$pipeline = $this->client->pipeline();
		
		if (isset($payloads['now']))
		{
			$this->prepareNow($queueName, $pipeline, $payloads['now']);
		}
		
		if (isset($payloads['delayed']))
		{
			$this->prepareDelayed($queueName, $pipeline, $payloads['delayed']);
		}
		
		$this->preparePayloads($queueName, $pipeline, $payloads);
		
		return $pipeline;
	}
	
	private function addZeroKeyIfEmpty(string $queueName): void
	{
		if ($this->client->llen(BucketNameBuilder::getNowKey($queueName)) == 0)
		{
			$this->client->rpush(BucketNameBuilder::getNowKey($queueName), [self::ZEROKEY]);
		}
	}
	
	private function getKeys(string $queueName, int $count, string $initialKey): array 
	{
		$dequeuingKeys[] = $initialKey;
		
		$pipeline = $this->client->pipeline();
		
		$pipeline->lrange(BucketNameBuilder::getNowKey($queueName), 0, $count - 1);
		$pipeline->ltrim(BucketNameBuilder::getNowKey($queueName), $count, -1);
		
		$response = $pipeline->execute();

		$dequeuingKeys = array_merge($dequeuingKeys, $response[0]);
		
		return array_unique($dequeuingKeys);
	}
	
	private function getPayloads(string $queueName, array $keys): array
	{
		if (!$keys)
			return [];
		
		$pipeline = $this->client->pipeline();
		
		$pipeline->hmget(BucketNameBuilder::getPayloadsKey($queueName), $keys);
		$pipeline->hdel(BucketNameBuilder::getPayloadsKey($queueName), $keys);
		
		$response = $pipeline->execute();
		
		$payloads = $response[0];

		return array_filter(array_combine($keys, $payloads));
	}

	
	public function initClient(IRedisConfig $config)
	{
		$this->client = new Client($config->getParameters(), $config->getOptions());
	}

	public function enqueue(string $queueName, array $payloads): array
	{
		$pipeline = $this->setupEnqueuePipeline($queueName, $payloads);
		
		$pipeline->execute();
		
		$now_keys = isset($payloads['now']) ? array_keys($payloads['now']) : [];
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
			$key = $this->client->blpop([BucketNameBuilder::getNowKey($queueName)], $waitSeconds);
			$key = $key[1];
		}
		else
		{
			$key = $this->client->lpop(BucketNameBuilder::getNowKey($queueName));
		}
		
		if (!$key)
			return null;
		
		return $key;
	}
	
	public function dequeueAll(string $queueName, int $count, string $initialKey): array
	{
		$keys = $this->getKeys($queueName, $count, $initialKey);

		return $this->getPayloads($queueName, $keys);
	}
	
	public function popDelayed(string $queueName): void
	{
		$delayed = $this->client->zrangebyscore(BucketNameBuilder::getDelayedKey($queueName), 
			0, TimeGenerator::getMs());

		if (!$delayed)
		{
			return;
		}

		$pipeline = $this->client->pipeline();
		
		$this->prepareNow($queueName, $pipeline, $delayed);
		
		$pipeline->zrem(BucketNameBuilder::getDelayedKey($queueName), $delayed);
		
		$pipeline->execute();
	}
	
	public function getFirstDelayed(string $queueName): array 
	{
		$result = $this->client->zrange(BucketNameBuilder::getDelayedKey($queueName), 
				0, 0, 'WITHSCORES');
		
		return $result ?: [];
	}
	
	public function countEnqueued(string $queueName): int
	{
		return $this->client->hlen(BucketNameBuilder::getPayloadsKey($queueName));
	}

	public function countDelayed(string $queueName): int
	{
		return $this->client->zcard(BucketNameBuilder::getDelayedKey($queueName));
	}
}