<?php
namespace DeepQueue\Module\Data\Serialization;


use DeepQueue\Payload;

use Serialization\Base\ISerializer;
use Serialization\Base\IJsonSerializer;
use Serialization\Base\Json\IPlainSerializer;
use Serialization\Exceptions\SerializationException;


class PlainPayloadSerializer implements IPlainSerializer
{
	/** @var ISerializer */
	private $payloadDataSerializer;


	/**
	 * @param $payload mixed
	 * @return mixed
	 */
	private function deserializePayload($payload)
	{
		if ($this->payloadDataSerializer instanceof IJsonSerializer)
		{
			return $this->payloadDataSerializer->asDataConstructor()->deserialize($payload);
		}
		
		return $this->payloadDataSerializer->deserialize($payload);
	}
	
	/**
	 * @param $payload mixed
	 * @return mixed
	 */
	private function serializePayload($payload)
	{
		if ($this->payloadDataSerializer instanceof IJsonSerializer)
		{
			return $this->payloadDataSerializer->asDataConstructor()->serialize($payload);
		}

		return $this->payloadDataSerializer->serialize($payload);
	}
	
	
	public function __construct(ISerializer $serializer)
	{
		$this->payloadDataSerializer = $serializer;
	}


	public function canSerialize($object): bool
	{
		return $object instanceof Payload;
	}

	/**
	 * @param mixed|Payload $object
	 * @param mixed $meta
	 * @return mixed
	 */
	public function serialize($object, &$meta)
	{
		$meta = get_class($object);
		
		
		return [
			'Key'		=> $object->Key,
			'Delay'		=> $object->Delay,
			'Payload'	=> $this->serializePayload($object->Payload)
		];
	}

	/**
	 * @param mixed $data
	 * @param mixed $meta
	 * @return mixed
	 */
	public function deserialize($data, $meta)
	{
		/** @var Payload $object */
		$object = new $meta;
		
		if (!($object instanceof Payload))
			throw new SerializationException("Class named $meta is not a Payload class");
		
		$payload = $object->fromArray((array)$data);
		
		$payload->Payload = $this->deserializePayload($payload->Payload);
		
		return $payload;
	}
}