<?php
namespace DeepQueue\Base\PreparedConfiguration;


use DeepQueue\DeepQueue;
use DeepQueue\Base\PreparedConfiguration\Plugins\IPluginConfiguration;

use Serialization\Base\ISerializer;


interface IPreparedQueueSetup
{
	public static function setup(IPluginConfiguration $configuration): DeepQueue;
	
	public static function InMemory(?ISerializer $serializer = null): DeepQueue;
}