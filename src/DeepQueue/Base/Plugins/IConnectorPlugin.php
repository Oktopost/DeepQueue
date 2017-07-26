<?php
namespace DeepQueue\Base\Plugins;


use DeepQueue\Base\IMetaData;
use DeepQueue\Base\IQueueObject;
use DeepQueue\Base\Connector\Remote\IRemoteQueueProvider;
use DeepQueue\Base\Plugins\ConnectorElements\IQueueManager;


interface IConnectorPlugin extends IRemoteQueueProvider, IConfigurable
{
	public function manager(string $queueName): IQueueManager;
}