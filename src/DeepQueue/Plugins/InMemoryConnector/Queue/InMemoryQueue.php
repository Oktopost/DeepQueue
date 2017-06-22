<?php
namespace DeepQueue\Plugins\InMemoryConnector\Queue;


use DeepQueue\Scope;
use DeepQueue\Base\Data\IPayloadConverter;
use DeepQueue\Base\Queue\Remote\IRemoteQueue;
use DeepQueue\Plugins\InMemoryConnector\Base\IInMemoryQueueConnector;


class InMemoryQueue implements IRemoteQueue
{
	private $name;

	/** @var IInMemoryQueueConnector */
	private $connector;
	
	/** @var IPayloadConverter */
	private $converter;

	
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
	
	
	public function __construct(string $name, IPayloadConverter $converter)
	{
		$this->connector = Scope::skeleton(IInMemoryQueueConnector::class);		
		$this->converter = $converter;
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

		return $this->converter->deserializeToWorkloads($payloads);
	}

	public function enqueue(array $payload): array
	{
		$serializedPayloads = $this->converter->serializeAll($payload);
		
		$this->connector->enqueue($this->name, $serializedPayloads);
		
		return array_keys($serializedPayloads);
	}
}