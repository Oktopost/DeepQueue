<?php
namespace DeepQueue\Plugins\InMemoryRemote\Base;


use DeepQueue\Base\IQueueObject;


/**
 * @skeleton
 */
interface IInMemoryRemoteStorage
{
	public function pushPayloads(string $queueName, array $payloads): array;
	public function pullPayloads(string $queueName, int $count): array;
	
	public function deletePayload(string $queueName, string $key): bool;
	
	public function countEnqueued(string $queueName): int;
}