<?php
namespace DeepQueue\Plugins\InMemoryRemote\Queue;


use DeepQueue\Scope;
use DeepQueue\Payload;
use DeepQueue\Base\Queue\Remote\IRemoteQueue;
use DeepQueue\Plugins\InMemoryRemote\Base\IInMemoryQueueConnector;


class InMemoryRemoteQueue implements IRemoteQueue
{
	private $name;

	/** @var IInMemoryQueueConnector */
	private $connector;
	
	
	private function createKeyValuePayloads(array $payloads): array
	{
		$keyValuePayloads = [];

		foreach ($payloads as $payload)
		{
			/** @var Payload $payload */
			$keyValuePayloads[$payload->Key] = $payload->Payload;
		}
	
		return $keyValuePayloads;
	}
	
	private function getPayloads(int $count): array 
	{
		return $this->connector->dequeue($this->name, $count);
	}
	
	private function getPayloadsWithWaiting(int $count, float $waitSeconds)
	{
		$endTime = (microtime(true) + $waitSeconds) * 1000;
		$nowTime = microtime(true) * 1000;

		$payloads = [];

		while ($nowTime < $endTime)
		{
			$payloads = $this->connector->dequeue($count);
			
			if ($payloads)
			{
				break;
			}
			
			$nowTime = microtime(true) * 1000;
		}
		
		return $payloads;
	}
	
	
	public function __construct(string $name)
	{
		$this->connector = Scope::skeleton(IInMemoryQueueConnector::class);
		$this->name = $name;
	}


	public function dequeueWorkload(int $count = 1, ?float $waitSeconds = null): array
	{
		if ($waitSeconds > 0)
		{
			$payloads = $this->getPayloadsWithWaiting($count, $waitSeconds);
		}
		else
		{
			$payloads = $this->getPayloads($count);
		}

		return $payloads;
	}

	public function enqueue(array $payload): array
	{
		$payloads = $this->createKeyValuePayloads($payload);
		
		$this->connector->enqueue($this->name, $payloads);
		
		return array_keys($payloads);
	}
}