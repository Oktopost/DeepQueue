<?php
namespace DeepQueue\PreparedConfiguration\Plugins;


use DeepQueue\Enums\QueueLoaderPolicy;
use DeepQueue\Base\Plugins\IManagerPlugin;
use DeepQueue\Base\Plugins\IConnectorPlugin;
use DeepQueue\Base\PreparedConfiguration\Plugins\IPluginConfiguration;
use DeepQueue\Plugins\Managers\RedisManager\RedisManager;
use DeepQueue\Plugins\Connectors\RedisConnector\RedisConnector;
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
		return new RedisManager($this->redisConfig);
	}

	public function getConnector(): IConnectorPlugin
	{
		return new RedisConnector($this->redisConfig);
	}

	public function getNotExistsPolicy(): int
	{
		return QueueLoaderPolicy::CREATE_NEW;
	}

	public function getSerializer(): ISerializer
	{
		return $this->serializer;
	}
}