<?php
namespace DeepQueue\Base\Serialization;


use Serialization\Serializers\JsonSerializer;
use Serialization\Json\Serializers\ArraySerializer;
use Serialization\Json\Serializers\LiteObjectSerializer;
use Serialization\Json\Serializers\PrimitiveSerializer;


trait TFromJson
{
	public function deserialize(string $data)
	{
		$serializer = new JsonSerializer();
		
		$serializer
			->add(new PrimitiveSerializer())
			->add(new ArraySerializer())
			->add(new LiteObjectSerializer());
		
		return $serializer->deserialize($data);
	}
}