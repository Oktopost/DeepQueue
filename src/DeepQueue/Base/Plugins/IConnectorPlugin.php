<?php
namespace DeepQueue\Base\Plugins;


use DeepQueue\Base\Connector\Remote\IRemoteQueueProvider;
use DeepQueue\Base\IDeepQueueConfig;
use DeepQueue\Base\IMetaData;
use DeepQueue\Base\IQueueObject;
use DeepQueue\Base\Plugins\RemoteElements\IMetaDataDAO;


interface IConnectorPlugin extends IRemoteQueueProvider
{
	public function getMetaData(IQueueObject $queueObject): IMetaData;
	
	public function setConfig(IDeepQueueConfig $config): void;
}