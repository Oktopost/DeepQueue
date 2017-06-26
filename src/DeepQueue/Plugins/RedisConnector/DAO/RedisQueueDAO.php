<?php
namespace DeepQueue\Plugins\RedisConnector\DAO;


use DeepQueue\Base\Config\IRedisConfig;
use DeepQueue\Plugins\RedisConnector\Base\IRedisQueueDAO;

use Predis\Client;
use Predis\Pipeline\Pipeline;


class RedisQueueDAO implements IRedisQueueDAO
{
	private const NOW_SUFFIX = 'now';
	private const DELAYED_SUFFIX = 'delayed';
	private const PAYLOADS_SUFFIX = 'payloads';
	
	/** @var Client	*/
	private $client;
	
	
	private function getNowKey(string $queueName): string
	{
		return "{$queueName}:" . self::NOW_SUFFIX;
	}
	
	private function getPayloadsKey(string $queueName): string 
	{
		return "{$queueName}:" . self::PAYLOADS_SUFFIX;
	}
	
	private function getDelayedKey(string $queueName): string
	{
		return "{$queueName}:" . self::DELAYED_SUFFIX;
	}
	
	private function prepareNow(string $queueName, Pipeline $pipeline, $payloads): void
	{
		if (isset($payloads['now']))
		{
			$pipeline->zadd($this->getNowKey($queueName), $payloads['now']);
		}
	}
	
	private function preparePayloads(string $queueName, Pipeline $pipeline, $payloads): void 
	{
		$pipeline->hmset($this->getPayloadsKey($queueName), $payloads['keyValue']);
	}
	
	private function prepareDelayed(string $queueName, Pipeline $pipeline, $payloads): void
	{
		if (isset($payloads['delayed']))
		{
			$delayed = [];
			
			foreach ($payloads['delayed'] as $key => $delay)
			{
				$delayed[$key] = (microtime(true) + $delay) * 1000;	
			}
			
			$pipeline->zadd($this->getDelayedKey($queueName), $delayed);
		}
	}
	
	private function setupPipeline(string $queueName, $payloads): Pipeline
	{
		$pipeline = $this->client->pipeline();
		
		$this->prepareNow($queueName, $pipeline, $payloads);
		$this->prepareDelayed($queueName, $pipeline, $payloads);
		$this->preparePayloads($queueName, $pipeline, $payloads);
		
		return $pipeline;
	}
	
	
	public function initClient(IRedisConfig $config)
	{
		$this->client = new Client($config->getParameters(), $config->getOptions());
	}

	public function enqueue(string $queueName, array $payloads): array
	{
		$pipeline = $this->setupPipeline($queueName, $payloads);
		
		$pipeline->execute();
		
		$now_keys = isset($payloads['now']) ? array_keys($payloads['now']) : [];
		$delayed_keys = isset($payloads['delayed']) ? array_keys($payloads['delayed']) : [];
		
		return array_merge($now_keys, $delayed_keys);
	}

	public function dequeue(string $queueName, int $count = 1): array
	{
		// TODO: Implement dequeue() method.
	}

	public function delete(string $queueName, array $payloadIds): bool
	{
		$pipeline = $this->client->pipeline();
		
		$pipeline->zrem($this->getNowKey($queueName), $payloadIds);
		$pipeline->zrem($this->getDelayedKey($queueName), $payloadIds);
		$pipeline->hdel($this->getPayloadsKey($queueName), $payloadIds);
		
		$result = $pipeline->execute();
		
		var_dump($result); die();
	}

	public function countEnqueued(string $queueName): int
	{
		$enqueued = $this->client->hlen($this->getPayloadsKey($queueName));
		
		var_dump($enqueued);
		
		return $enqueued;
	}

	public function countDelayed(string $queueName): int
	{
		$count = $this->client->zcard($this->getDelayedKey($queueName));
		
		var_dump($count);
		
		return $count;
	}
}