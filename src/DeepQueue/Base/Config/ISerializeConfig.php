<?php
namespace DeepQueue\Base\Config;


use DeepQueue\Base\IDeepQueueConfig;

use Serialization\Base\ISerializer;


interface ISerializeConfig
{
	public function setSerializer(ISerializer $serializer): IDeepQueueConfig;
	public function serializer(): ISerializer;
}