<?php
namespace DeepQueue\PreparedConfiguration\Plugins;


use DeepQueue\Base\Plugins\IManagerPlugin;
use DeepQueue\Base\Plugins\IConnectorPlugin;
use DeepQueue\Base\PreparedConfiguration\Plugins\IPluginConfiguration;
use DeepQueue\Enums\QueueLoaderPolicy;
use DeepQueue\Plugins\InMemoryManager\InMemoryManager;
use DeepQueue\Plugins\InMemoryConnector\InMemoryConnector;


class InMemoryConfiguration implements IPluginConfiguration
{
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
}