<?php
namespace DeepQueue\Module\Data;


use DeepQueue\Scope;
use DeepQueue\Payload;
use DeepQueue\Base\Data\IPayloadConverter;
use DeepQueue\Base\Ids\IIdGenerator;
use DeepQueue\Module\Data\Serialization\PayloadSerializer;

use DeepQueue\Workload;
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
	

	public function serializeAll(array $payloads): array
	{
		$keyValuePayloads = [];

		foreach ($payloads as $payload)
		{
			$key = null;
			
			if (!$payload->Key)
			{
				$key = $this->createKey();
			}
			
			/** @var Payload $payload */
			$keyValuePayloads[$payload->Key ?: $key] = $this->serializer->serialize($payload);
		}
	
		return $keyValuePayloads;
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

	public function deserializeToWorkloads(array $payloads): array
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

	public function getDelayed(array $payloads): array
	{
		$delayed = [];

		foreach ($payloads as $payload)
		{
			/** @var Payload $payload */
			if ($payload->hasDelay())
			{
				$delayed[$payload->Key] = $payload->Delay;
			}
		}
		
		return $delayed;
	}

	public function getImmediately(array $payloads): array
	{
		$immediately = [];

		foreach ($payloads as $payload)
		{
			/** @var Payload $payload */
			if (!$payload->hasDelay())
			{
				$immediately[] = $payload->Key;
			}
		}
		
		return $immediately;
	}
}