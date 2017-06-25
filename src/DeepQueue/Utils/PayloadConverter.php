<?php
namespace DeepQueue\Utils;


use DeepQueue\Exceptions\InvalidUsageException;
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
	
	private function checkType($object): void
	{
		if (!$object instanceof Payload)
		{
			throw new InvalidUsageException('Prepared payload must be instance of Payload');
		}
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
			$this->checkType($payload);
			
			$key = null;
			
			if (!$payload->Key)
			{
				$key = $this->createKey();
			}
			
			$preparedPayloads['keyValue'][$payload->Key ?: $key] = $this->serializer->serialize($payload);
			
			if (!$payload->hasDelay())
			{
				$preparedPayloads['now'][] = $payload->Key ?: $key;
			}
			else
			{
				$preparedPayloads['delayed'][$payload->Key] = $payload->Delay;
			}
		}
	
		return $preparedPayloads;
	}

	/**
	 * @return Payload[]|array
	 */
	public function deserializeAll(array $payloads): array
	{
		$deserializedPayloads = [];
		
		foreach ($payloads as $key => $payload)
		{
			$deserializedPayloads[$key] = $this->serializer->deserialize($payload);
		}
		
		return $deserializedPayloads;
	}

	/**
	 * @return Workload[]|array
	 */
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