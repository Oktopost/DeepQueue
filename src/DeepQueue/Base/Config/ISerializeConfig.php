<?php
namespace DeepQueue\Base\Config;


use DeepQueue\Base\IDeepQueueConfig;
use DeepQueue\Base\Data\IPayloadConverter;

use Serialization\Base\ISerializer;


interface ISerializeConfig
{
	public function setPayloadDataSerializer(ISerializer $serializer): IDeepQueueConfig;
	public function converter(): IPayloadConverter;
}