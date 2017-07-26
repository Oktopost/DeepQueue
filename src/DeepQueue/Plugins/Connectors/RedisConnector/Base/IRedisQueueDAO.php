<?php
namespace DeepQueue\Plugins\Connectors\RedisConnector\Base;


use DeepQueue\Base\Config\IRedisConfig;


/**
 * @skeleton
 */
interface IRedisQueueDAO
{
	public function initClient(IRedisConfig $config): void;
	public function enqueue(string $queueName, array $payloads): array;
	public function dequeueInitialKey($queueName, $waitSeconds): ?string;
	public function dequeueAll(string $queueName, int $count, ?string $initialKey = null): array;
	public function popDelayed(string $queueName): void;
	public function getFirstDelayed(string $queueName): array;
		
	public function countEnqueued(string $queueName): int;
	public function countDelayed(string $queueName): int;
	
	public function clearQueue(string $queueName): void;
}