<?php
namespace DeepQueue\Plugins\RedisConnector\Base;


use DeepQueue\Base\Config\IRedisConfig;


/**
 * @skeleton
 */
interface IRedisQueueDAO
{
	public function initClient(IRedisConfig $config);
	public function enqueue(string $queueName, array $payloads): array;
	public function dequeue(string $queueName, int $count = 1): array;
	public function delete(string $queueName, array $payloadIds): bool;
	
	public function countEnqueued(string $queueName): int;
	public function countDelayed(string $queueName): int;
}