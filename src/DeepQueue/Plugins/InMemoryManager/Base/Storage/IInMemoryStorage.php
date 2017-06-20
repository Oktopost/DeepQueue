<?php
namespace DeepQueue\Plugins\InMemoryManager\Base\Storage;


use DeepQueue\Base\IQueueObject;


/**
 * @skeleton
 */
interface IInMemoryStorage
{
	public function pushQueue(IQueueObject $queue): IQueueObject;
	public function pullQueue(string $name): ?IQueueObject;
	
	public function pushPayloads(string $queueName, array $payloads): array;
	public function pullPayloads(string $queueName, int $count): array;
	
	public function deletePayload(string $queueName, string $key): bool;
}