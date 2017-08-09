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
	public function dequeueInitialKey(string $queueName, int $waitSeconds): ?string;
	public function dequeueAll(string $queueName, int $count, ?string $initialKey = null): array;
	public function popDelayed(string $queueName, float $bufferOffset = 0.0, int $packageSize = 0): void;
	public function getFirstDelayed(string $queueName): array;
	public function getDelayedElementByIndex(string $queueName, int $index): array;
}