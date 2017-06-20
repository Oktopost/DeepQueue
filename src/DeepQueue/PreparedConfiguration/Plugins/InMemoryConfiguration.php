<?php
namespace DeepQueue\PreparedConfiguration\Plugins;


use DeepQueue\Base\Plugins\IManagerPlugin;
use DeepQueue\Base\Plugins\IRemotePlugin;
use DeepQueue\Base\PreparedConfiguration\Plugins\IPluginConfiguration;
use DeepQueue\Enums\QueueLoaderPolicy;
use DeepQueue\Plugins\InMemoryManager\InMemoryManager;
use DeepQueue\Plugins\InMemoryRemote\InMemoryRemote;


class InMemoryConfiguration implements IPluginConfiguration
{
	public function getManager(): IManagerPlugin
	{
		return new InMemoryManager();
	}

	public function getRemote(): IRemotePlugin
	{
		return new InMemoryRemote();
	}

	public function getNotExistsPolicy(): int
	{
		return QueueLoaderPolicy::CREATE_NEW;
	}
}