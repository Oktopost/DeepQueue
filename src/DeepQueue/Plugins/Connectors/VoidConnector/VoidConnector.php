<?php
namespace DeepQueue\Plugins\Connectors\VoidConnector;


use DeepQueue\Base\IMetaData;
use DeepQueue\Base\IQueueObject;
use DeepQueue\Base\IDeepQueueConfig;
use DeepQueue\Base\Queue\Remote\IRemoteQueue;
use DeepQueue\Manager\MetaData;
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

	public function getMetaData(IQueueObject $queueObject): IMetaData
	{
		return new MetaData();
	}

	public function getQueue(string $name): IRemoteQueue
	{
		return new VoidQueue();
	}
}