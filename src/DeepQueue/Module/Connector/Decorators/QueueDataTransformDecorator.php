<?php
namespace DeepQueue\Module\Connector\Decorators;


use DeepQueue\Base\Connector\Decorator\IRemoteQueueDecorator;
use DeepQueue\Module\Serialization\JsonSerializer;
use DeepQueue\Payload;
use DeepQueue\Workload;


class QueueDataTransformDecorator implements IRemoteQueueDecorator
{
	use \DeepQueue\Module\Connector\Decorators\Base\TRemoteQueueDecorator;

	
	/** @var JsonSerializer */
	private $serializer = null;
	
	
	private function getSerializer(): JsonSerializer
	{
		if (!$this->serializer)
		{
			$this->serializer = new JsonSerializer();
		}
		
		return $this->serializer;
	}
	
	private function serializePayloadData(array $payloads): array
	{
		foreach ($payloads as $payload)
		{
			/** @var Payload $payload */
			$payload->Payload = $this->getSerializer()->serialize($payload->Payload);
		}
	
		return $payloads;
	}
	
	private function deserializeToWorkloads(array $payloads): array
	{
		$workloads = [];
		
		foreach ($payloads as $key => $payload)
		{
			$workload = new Workload($this->getSerializer()->deserialize($payload));
			$workload->Id = $key;
			
			$workloads[] = $workload;
		}
	
		return $workloads;
	}
	
	
	public function dequeueWorkload(int $count = 1, ?float $waitSeconds = null): array
	{
		$payloads = $this->getRemoteQueue()->dequeueWorkload($count, $waitSeconds);
		
		return $this->deserializeToWorkloads($payloads);
	}

	public function enqueue(array $payload): array
	{
		$payload = $this->serializePayloadData($payload);
		
		return $this->getRemoteQueue()->enqueue($payload);
	}
}