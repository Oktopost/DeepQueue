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
		
		$payloads = array_splice($this->payloads[$queueName], 0, $count);
		
		return $payloads;
	}
	
	public function countEnqueued(string $queueName): int
	{
		return isset($this->payloads[$queueName]) ? sizeof($this->payloads[$queueName]) : 0;
	}
	
	public function clearQueue(string $queueName): void
	{
		if (isset($this->payloads[$queueName]))
		{
			unset($this->payloads[$queueName]);
		}
	}

	public function cache(): array
	{
		return $this->payloads;
	}

	public function flushCache()
	{
		$this->payloads = [];
	}
}