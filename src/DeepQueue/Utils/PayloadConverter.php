<?php
namespace DeepQueue\Utils;


use DeepQueue\Exceptions\InvalidUsageException;
use DeepQueue\Scope;
use DeepQueue\Payload;
use DeepQueue\Workload;
use DeepQueue\Base\Utils\IPayloadConverter;
use DeepQueue\Base\Utils\IIdGenerator;
use DeepQueue\Module\Serialization\PayloadSerializer;

use Serialization\Base\ISerializer;
use Serialization\Json\Serializers\PrimitiveSerializer;
use Serialization\Serializers\JsonSerializer;


class PayloadConverter implements IPayloadConverter
{
	private const UNIQUE_PREFIX = 'unq';
	
	
	/** @var ISerializer */
	private $serializer;
	
	/** @var IIdGenerator */
	private $idGenerator;
	
	
	private function createKey()
	{
		return self::UNIQUE_PREFIX . $this->idGenerator->get();
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
			
			$key = $payload->Key ?: $this->createKey();
			
			$preparedPayloads['keyValue'][$key] = $this->serializer->serialize($payload);
			
			if (!$payload->hasDelay())
			{
				$preparedPayloads['now'][] = $key;
			}
			else
			{
				$preparedPayloads['delayed'][$key] = $payload->Delay;
			}
		}
	
		return $preparedPayloads;
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