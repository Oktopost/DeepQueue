<?php
namespace DeepQueue\Utils;


use DeepQueue\Scope;
use DeepQueue\Payload;
use DeepQueue\Workload;
use DeepQueue\Base\Utils\IPayloadConverter;
use DeepQueue\Base\Ids\IIdGenerator;
use DeepQueue\Module\Serialization\PayloadSerializer;

use Serialization\Base\ISerializer;
use Serialization\Json\Serializers\PrimitiveSerializer;
use Serialization\Serializers\JsonSerializer;


class PayloadConverter implements IPayloadConverter
{
	/** @var ISerializer */
	private $serializer;
	
	/** @var IIdGenerator */
	private $idGenerator;
	
	
	private function createKey()
	{
		return $this->idGenerator->get();
	}
	
	
	public function __construct(ISerializer $payloadDataSerializer)
	{
		$this->idGenerator = Scope::skeleton(IIdGenerator::class);
		
		$this->serializer = new JsonSerializer();
		
		$this->serializer
			->add(new PrimitiveSerializer())
			->add(new PayloadSerializer($payloadDataSerializer));
	}
	

	public function prepareAll(array $payloads): array
	{
		$preparedPayloads = [];

		/** @var Payload $payload */
		foreach ($payloads as $payload)
		{
			$key = null;
			
			if (!$payload->Key)
			{
				$key = $this->createKey();
			}
			
			$preparedPayloads['keyValue'][$payload->Key ?: $key] = $this->serializer->serialize($payload);
			
			if (!$payload->hasDelay())
			{
				$preparedPayloads['immediately'][] = $payload->Key ?: $key;
			}
			else
			{
				$preparedPayloads['delayed'][$payload->Key] = $payload->Delay;
			}
		}
	
		return $preparedPayloads;
	}

	public function deserializeAll(array $payloads): array
	{
		$deserializedPayloads = [];
		
		foreach ($payloads as $key => $payload)
		{
			$deserializedPayloads[] = $this->serializer->deserialize($payload);
		}
		
		return $deserializedPayloads;
	}

	public function getWorkloads(array $payloads): array
	{
		$workloads = [];
		
		foreach ($payloads as $key => $payload)
		{
			/** @var Payload $payloadObject */
			$payloadObject = $this->serializer->deserialize($payload);
			
			$workload = new Workload($payloadObject->Payload);
			$workload->Id = $key;
			
			$workloads[] = $workload;
		}
		
		return $workloads;
	}
}