<?php
namespace DeepQueue\Plugins\Connectors\VoidConnector;


use DeepQueue\Base\IMetaData;
use DeepQueue\Base\IQueueObject;
use DeepQueue\Base\IDeepQueueConfig;
use DeepQueue\Base\Plugins\ConnectorElements\IQueueManager;
use DeepQueue\Base\Queue\Remote\IRemoteQueue;
use DeepQueue\Manager\MetaData;
use DeepQueue\Plugins\Connectors\VoidConnector\Manager\VoidQueueManager;
use DeepQueue\Plugins\Connectors\VoidConnector\Queue\VoidQueue;
use DeepQueue\Plugins\Connectors\VoidConnector\Base\IVoidConnector;


/**
 * Only for testing purposes, every passed data will be ignored
 */
class VoidConnector implements IVoidConnector
{
	public function setDeepConfig(IDeepQueueConfig $config): void
	{
		return;
	}

	public function manager(string $queueName): IQueueManager
	{
		return new VoidQueueManager();
	}

	public function getQueue(string $name): IRemoteQueue
	{
		return new VoidQueue();
	}
}