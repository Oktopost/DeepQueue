<?php
namespace DeepQueue\Plugins\Connectors\InMemoryConnector\Base;


/**
 * @skeleton
 */
interface IInMemoryQueueDAO
{
	public function enqueue(string $queueName, array $payloads): array;
	public function dequeue(string $queueName, int $count = 1): array;

	public function countEnqueued(string $queueName): int;
	
	public function clearQueue(string $queueName): void;
}