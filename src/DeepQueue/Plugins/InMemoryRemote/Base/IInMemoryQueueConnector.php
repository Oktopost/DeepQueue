<?php
namespace DeepQueue\Plugins\InMemoryRemote\Base;


/**
 * @skeleton
 */
interface IInMemoryQueueConnector
{
	public function enqueue(string $queueName, array $payloads): array;
	public function dequeue(string $queueName, int $count = 1): array;
	
	public function delete(string $queueName, string $payloadId): bool;
}