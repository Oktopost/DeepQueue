<?php
namespace DeepQueue\Plugins\InMemoryConnector\Queue;


use DeepQueue\Scope;
use DeepQueue\Payload;
use DeepQueue\Workload;
use DeepQueue\Utils\PayloadConverter;
use DeepQueue\Base\Queue\Remote\IRemoteQueue;
use DeepQueue\Plugins\InMemoryConnector\Base\IInMemoryQueueConnector;

use Serialization\Base\ISerializer;


class InMemoryQueue implements IRemoteQueue
{
	private $name;

	/** @var IInMemoryQueueConnector */
	private $connector;
	
	/** @var PayloadConverter */
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
			$payloads = $this->getPayloads($count);
			
			if ($payloads)
			{
				break;
			}
			
			$nowTime = microtime(true) * 1000;
		}
		
		return $payloads;
	}
	
	
	public function __construct(string $name, ISerializer $serializer)
	{
		$this->connector = Scope::skeleton(IInMemoryQueueConnector::class);
		$this->converter = new PayloadConverter($serializer);
		$this->name = $name;
	}

	/**
	 * @return Workload[]
	 */
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

		return $this->converter->getWorkloads($payloads);
	}

	/**
	 * @param Payload[] $payload
	 * @return ?string[] IDs for each payload
	 */
	public function enqueue(array $payload): array
	{
		$prepared = $this->converter->prepareAll($payload);
		
		return $this->connector->enqueue($this->name, $prepared['keyValue']);
	}
}