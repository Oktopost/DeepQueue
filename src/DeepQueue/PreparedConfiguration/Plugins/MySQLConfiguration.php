<?php
namespace DeepQueue\PreparedConfiguration\Plugins;


use DeepQueue\Base\Plugins\IManagerPlugin;
use DeepQueue\Base\Plugins\IConnectorPlugin;
use DeepQueue\Base\PreparedConfiguration\Plugins\IPluginConfiguration;
use DeepQueue\PreparedConfiguration\Plugins\Managers\Managers;
use DeepQueue\PreparedConfiguration\Plugins\Connectors\Connectors;
use DeepQueue\PreparedConfiguration\Plugins\Config\DefaultSerializer;

use Serialization\Base\ISerializer;


class MySQLConfiguration implements IPluginConfiguration
{
	/** @var ISerializer */
	private $serializer;
	
	private $mysqlConfig = [];
	
	
	public function __construct(array $mysqlConfig, ?ISerializer $serializer = null)
	{
		$this->mysqlConfig = $mysqlConfig;
		$this->serializer = $serializer ?: DefaultSerializer::get();
	}
	
	
	public function getManager(): IManagerPlugin
	{
		return Managers::MySQL($this->mysqlConfig);
	}

	public function getConnector(): IConnectorPlugin
	{
		return Connectors::MySQL($this->mysqlConfig);
	}
	
	public function getSerializer(): ISerializer
	{
		return $this->serializer;
	}
}