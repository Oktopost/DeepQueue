<?php
namespace DeepQueue\PreparedConfiguration\Plugins;


use DeepQueue\Enums\QueueLoaderPolicy;
use DeepQueue\Base\Plugins\IManagerPlugin;
use DeepQueue\Base\Plugins\IConnectorPlugin;
use DeepQueue\Base\PreparedConfiguration\Plugins\IPluginConfiguration;
use DeepQueue\Plugins\Managers\MySQLManager\MySQLManager;
use DeepQueue\Plugins\Connectors\MySQLConnector\MySQLConnector;
use DeepQueue\Plugins\Connectors\RedisConnector\RedisConnector;
use DeepQueue\Plugins\Connectors\FallbackConnector\FallbackConnector;
use DeepQueue\PreparedConfiguration\Plugins\Config\DefaultSerializer;

use Serialization\Base\ISerializer;


class FallbackMySQLConfiguration implements IPluginConfiguration
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
		return new MySQLManager($this->mysqlConfig);
	}

	public function getConnector(): IConnectorPlugin
	{
		return new FallbackConnector(new RedisConnector($this->redisConfig), new MySQLConnector($this->mysqlConfig));
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