<?php
namespace DeepQueue\PreparedConfiguration;


use DeepQueue\Base\PreparedConfiguration\IPreparedQueueSetup;
use DeepQueue\Base\PreparedConfiguration\Plugins\IPluginConfiguration;
use DeepQueue\DeepQueue;
use DeepQueue\PreparedConfiguration\Plugins\InMemoryConfiguration;
use Serialization\Base\ISerializer;


class PreparedQueueSetup implements IPreparedQueueSetup
{
	use \Objection\TStaticClass;
	
	
	public static function setup(IPluginConfiguration $config): DeepQueue
	{
		$deepQueue = new DeepQueue();
		$deepQueue->config()
			->setQueueNotExistsPolicy($config->getNotExistsPolicy())
			->setManagerPlugin($config->getManager())
			->setConnectorPlugin($config->getConnector());
		
		if ($config->getSerializer())
		{
			$deepQueue->config()
				->setSerializer($config->getSerializer());
		}
		
		return $deepQueue;
	}
	
	public static function InMemory(?ISerializer $serializer = null): DeepQueue
	{
		return self::setup(new InMemoryConfiguration($serializer));
	}
}