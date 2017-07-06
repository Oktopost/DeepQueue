<?php
namespace DeepQueue\Plugins\Connectors\InMemoryConnector\Base;


/**
 * @skeleton
 */
interface IInMemoryQueueDAO
{
	public function enqueue(string $queueName, array $payloads): array;
	public function dequeue(string $queueName, int $count = 1): array;
	
	public function delete(string $queueName, string $payloadId): bool;
	
	public function countEnqueued(string $queueName): int;
}