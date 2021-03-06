<?php
namespace DeepQueue\PreparedConfiguration\Plugins;


use DeepQueue\Base\Plugins\IManagerPlugin;
use DeepQueue\Base\Plugins\IConnectorPlugin;
use DeepQueue\Base\PreparedConfiguration\Plugins\IPluginConfiguration;
use DeepQueue\PreparedConfiguration\Plugins\Managers\Managers;
use DeepQueue\PreparedConfiguration\Plugins\Connectors\Connectors;
use DeepQueue\PreparedConfiguration\Plugins\Config\DefaultSerializer;

use Serialization\Base\ISerializer;


class RedisConfiguration implements IPluginConfiguration
{
	/** @var ISerializer */
	private $serializer;
	
	private $redisConfig = [];
	
	
	public function __construct(array $redisConfig, ?ISerializer $serializer = null)
	{
		$this->redisConfig = $redisConfig;
		$this->serializer = $serializer ?: DefaultSerializer::get();
	}
	
	public function getManager(): IManagerPlugin
	{
		return Managers::Redis($this->redisConfig);
	}

	public function getConnector(): IConnectorPlugin
	{
		return Connectors::Redis($this->redisConfig);
	}

	public function getSerializer(): ISerializer
	{
		return $this->serializer;
	}
}