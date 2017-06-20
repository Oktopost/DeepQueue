<?php
namespace DeepQueue\PreparedConfiguration;


use DeepQueue\Base\PreparedConfiguration\IPreparedQueueSetup;
use DeepQueue\Base\PreparedConfiguration\Plugins\IPluginConfiguration;
use DeepQueue\DeepQueue;


class PreparedQueueSetup implements IPreparedQueueSetup
{
	use \Objection\TStaticClass;
	
	
	public static function setup(IPluginConfiguration $config): DeepQueue
	{
		$deepQueue = new DeepQueue();
		$deepQueue->config()
			->setQueueNotExistsPolicy($config->getNotExistsPolicy())
			->setManagerPlugin($config->getManager())
			->setRemotePlugin($config->getRemote());
		
		return $deepQueue;
	}
}