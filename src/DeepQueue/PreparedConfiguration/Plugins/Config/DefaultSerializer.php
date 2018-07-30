<?php
namespace DeepQueue\PreparedConfiguration\Plugins\Config;


use Serialization\Base\ISerializer;
use Serialization\Serializers\JsonSerializer;
use Serialization\Json\Serializers\ArraySerializer;
use Serialization\Json\Serializers\PrimitiveSerializer;

use Traitor\TStaticClass;


class DefaultSerializer
{
	use TStaticClass;
	
	
	public static function get(): ISerializer
	{
		return (new JsonSerializer())
			->add(new PrimitiveSerializer())
			->add(new ArraySerializer());
	}
}