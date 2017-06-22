<?php
namespace DeepQueue\PreparedConfiguration\Plugins;


use DeepQueue\Enums\QueueLoaderPolicy;
use DeepQueue\Base\Plugins\IManagerPlugin;
use DeepQueue\Base\Plugins\IConnectorPlugin;
use DeepQueue\Base\PreparedConfiguration\Plugins\IPluginConfiguration;
use DeepQueue\Plugins\InMemoryManager\InMemoryManager;
use DeepQueue\Plugins\InMemoryConnector\InMemoryConnector;

use Serialization\Base\ISerializer;


class InMemoryConfiguration implements IPluginConfiguration
{
	/** @var ISerializer */
	private $serializer = null;
	
	
	public function __construct(?ISerializer $serializer = null)
	{
		if ($serializer)
		{
			$this->serializer = $serializer;
		}
	}


	public function getManager(): IManagerPlugin
	{
		return new InMemoryManager();
	}

	public function getConnector(): IConnectorPlugin
	{
		return new InMemoryConnector();
	}

	public function getNotExistsPolicy(): int
	{
		return QueueLoaderPolicy::CREATE_NEW;
	}
	
	public function getSerializer(): ?ISerializer
	{
		return $this->serializer;
	}
}