<?php
namespace DeepQueue\Plugins\Connectors\VoidConnector\Manager;


use DeepQueue\Base\IMetaData;
use DeepQueue\Manager\MetaData;
use DeepQueue\Base\Plugins\ConnectorElements\IQueueManager;


class VoidQueueManager implements IQueueManager
{
	public function setQueueName(string $queueName): void
	{
		return;
	}

	public function getMetaData(): IMetaData
	{
		return new MetaData();
	}

	public function clearQueue(): void
	{
		return;
	}
}