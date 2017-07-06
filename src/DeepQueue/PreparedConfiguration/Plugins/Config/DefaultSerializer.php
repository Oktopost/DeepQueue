<?php
namespace DeepQueue\PreparedConfiguration\Plugins\Config;


use Serialization\Base\ISerializer;
use Serialization\Serializers\JsonSerializer;
use Serialization\Json\Serializers\ArraySerializer;
use Serialization\Json\Serializers\PrimitiveSerializer;


class DefaultSerializer
{
	use \Objection\TStaticClass;
	
	
	public static function get(): ISerializer
	{
		return (new JsonSerializer())
			->add(new PrimitiveSerializer())
			->add(new ArraySerializer());
	}
}