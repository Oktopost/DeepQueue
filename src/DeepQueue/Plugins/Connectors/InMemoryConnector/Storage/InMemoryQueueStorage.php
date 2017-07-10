<?php
namespace DeepQueue\Plugins\Connectors\InMemoryConnector\Storage;


use DeepQueue\Plugins\Connectors\InMemoryConnector\Base\IInMemoryQueueStorage;


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
		
		$payloads = array_slice($this->payloads[$queueName], 0, $count);
		
		foreach ($payloads as $key => $payload)
		{
			unset($this->payloads[$queueName][$key]);
		}
		
		return $payloads;
	}
	
	public function countEnqueued(string $queueName): int
	{
		return isset($this->payloads[$queueName]) ? sizeof($this->payloads[$queueName]) : 0;
	}
}