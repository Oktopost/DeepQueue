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


class InMemoryConfiguration implements IPluginConfiguration
{
	/** @var ISerializer */
	private $serializer;
	
	
	public function __construct(?ISerializer $serializer = null)
	{
		$this->serializer = $serializer ?: DefaultSerializer::get();
	}


	public function getManager(): IManagerPlugin
	{
		return Managers::InMemory();
	}

	public function getConnector(): IConnectorPlugin
	{
		return Connectors::InMemory();
	}

	public function getSerializer(): ISerializer
	{
		return $this->serializer;
	}
}