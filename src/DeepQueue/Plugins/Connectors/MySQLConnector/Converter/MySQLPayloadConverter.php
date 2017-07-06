<?php
namespace DeepQueue\Plugins\Connectors\MySQLConnector\Converter;


use DeepQueue\Scope;
use DeepQueue\Payload;
use DeepQueue\Workload;
use DeepQueue\Base\Ids\IIdGenerator;
use DeepQueue\Exceptions\InvalidUsageException;
use DeepQueue\Module\Serialization\PayloadSerializer;
use DeepQueue\Plugins\Connectors\MySQLConnector\Base\Converter\IMySQLPayloadConverter;

use Serialization\Base\ISerializer;
use Serialization\Serializers\JsonSerializer;
use Serialization\Json\Serializers\PrimitiveSerializer;


class MySQLPayloadConverter implements IMySQLPayloadConverter
{
	/** @var ISerializer */
	private $serializer;
	
	/** @var IIdGenerator */
	private $idGenerator;
	
	
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
	
	public function prepareAll(string $queueName, array $payloads): array
	{
		$prepared = [];

		/** @var Payload $payload */
		foreach ($payloads as $payload)
		{
			$this->checkType($payload);
			
			$key = $payload->Key ?: $this->idGenerator->get();
			
			$dequeueTime = $payload->hasDelay() ? time() + (int)floor($payload->Delay) : time();
			
			$prepared['payloads'][] = [
				'Id'			=> $key,
				'QueueName'		=> $queueName,
				'Payload'		=> $this->serializer->serialize($payload)
			];
			
			$prepared['enqueue'][] = [
				'Id'			=> $key,
				'QueueName'		=> $queueName,
				'DequeueTime'	=> date('Y-m-d H:i:s', $dequeueTime),
			];
		}
	
		return $prepared;
	}

	public function getWorkloads(array $payloads): array
	{
		$workloads = [];
		
		foreach ($payloads as $payload)
		{
			/** @var Payload $payloadObject */
			$payloadObject = $this->serializer->deserialize($payload['Payload']);
			
			$workload = new Workload($payloadObject->Payload);
			$workload->Id = $payload['Id'];
			
			$workloads[] = $workload;
		}
		
		return $workloads;
	}
}