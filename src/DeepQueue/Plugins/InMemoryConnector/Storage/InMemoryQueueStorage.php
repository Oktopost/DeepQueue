<?php
namespace DeepQueue\Plugins\InMemoryConnector\Storage;


use DeepQueue\Plugins\InMemoryConnector\Base\IInMemoryQueueStorage;


/**
 * @unique
 * @autoload
 */
class InMemoryQueueStorage implements IInMemoryQueueStorage
{
	/** @var string[]|array */
	private $payloads = [];
	
	
	public function pushPayloads(string $queueName, array $payloads): array
	{
		if (!isset($this->payloads[$queueName]))
		{
			$this->payloads[$queueName] = [];
		}
		
		$this->payloads[$queueName] = array_merge($this->payloads[$queueName], $payloads);
		
		return array_keys($payloads);
	}

	public function pullPayloads(string $queueName, int $count): array
	{
		if (!isset($this->payloads[$queueName]) || !$this->payloads[$queueName])
		{
			return [];
		}
		
		return array_slice($this->payloads[$queueName], 0, $count);
	}
	
	public function deletePayload(string $queueName, string $key): bool
	{
		if (!isset($this->payloads[$queueName]) || !isset($this->payloads[$queueName][$key]))
		{
			return false;
		}
		
		unset($this->payloads[$queueName][$key]);
		
		return true;
	}
	
	public function countEnqueued(string $queueName): int
	{
		return isset($this->payloads[$queueName]) ? sizeof($this->payloads[$queueName]) : 0;
	}
}