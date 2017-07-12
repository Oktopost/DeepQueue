<?php
namespace DeepQueue\PreparedConfiguration\Plugins;


use DeepQueue\Base\Plugins\IManagerPlugin;
use DeepQueue\Base\Plugins\IConnectorPlugin;
use DeepQueue\Base\PreparedConfiguration\Plugins\IPluginConfiguration;
use DeepQueue\PreparedConfiguration\Plugins\Managers\Managers;
use DeepQueue\PreparedConfiguration\Plugins\Connectors\Connectors;
use DeepQueue\PreparedConfiguration\Plugins\Config\DefaultSerializer;

use Serialization\Base\ISerializer;

use Squid\MySql\IMySqlConnector;


class FallbackCachedConfiguration implements IPluginConfiguration
{
	/** @var ISerializer */
	private $serializer;
	
	private $mysqlConfig = [];
	private $redisConfig = [];
	
	
	/**
	 * @param array|IMySqlConnector $mysqlConfig
	 */
	public function __construct(array $redisConfig, $mysqlConfig, ?ISerializer $serializer = null)
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