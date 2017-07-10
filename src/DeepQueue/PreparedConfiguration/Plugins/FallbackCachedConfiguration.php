<?php
namespace DeepQueue\PreparedConfiguration\Plugins;


use DeepQueue\Enums\QueueLoaderPolicy;
use DeepQueue\Base\Plugins\IManagerPlugin;
use DeepQueue\Base\Plugins\IConnectorPlugin;
use DeepQueue\PreparedConfiguration\Plugins\Managers\Managers;
use DeepQueue\PreparedConfiguration\Plugins\Connectors\Connectors;
use DeepQueue\PreparedConfiguration\Plugins\Config\DefaultSerializer;
use DeepQueue\Base\PreparedConfiguration\Plugins\IPluginConfiguration;

use Serialization\Base\ISerializer;


class FallbackCachedConfiguration implements IPluginConfiguration
{
	/** @var ISerializer */
	private $serializer;
	
	private $mysqlConfig = [];
	private $redisConfig = [];
	
	
	public function __construct(array $mysqlConfig, array $redisConfig, ?ISerializer $serializer = null)
	{
		$this->mysqlConfig = $mysqlConfig;
		$this->redisConfig = $redisConfig;
		$this->serializer = $serializer ?: DefaultSerializer::get();
	}
	
	
	public function getManager(): IManagerPlugin
	{
		return Managers::Cached(Managers::MySQL($this->mysqlConfig), Managers::Redis($this->redisConfig));
	}

	public function getConnector(): IConnectorPlugin
	{
		return Connectors::Fallback(Connectors::Redis($this->redisConfig), Connectors::MySQL($this->mysqlConfig));
	}

	public function getSerializer(): ISerializer
	{
		return $this->serializer;
	}
}