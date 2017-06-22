<?php
namespace DeepQueue\Plugins\InMemoryConnector\Base;


use DeepQueue\Base\IQueueObject;


/**
 * @skeleton
 */
interface IInMemoryQueueStorage
{
	public function pushPayloads(string $queueName, array $payloads): array;
	public function pullPayloads(string $queueName, int $count): array;
	
	public function deletePayload(string $queueName, string $key): bool;
	
	public function countEnqueued(string $queueName): int;
}