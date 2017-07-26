<?php
namespace DeepQueue\Plugins\Connectors\InMemoryConnector\Base;


use DeepQueue\Base\IQueueObject;


/**
 * @skeleton
 */
interface IInMemoryQueueStorage
{
	public function pushPayloads(string $queueName, array $payloads): array;
	public function pullPayloads(string $queueName, int $count): array;

	public function countEnqueued(string $queueName): int;
	
	public function clearQueue(string $queueName): void;
	
	public function cache(): array;
	public function flushCache();
}