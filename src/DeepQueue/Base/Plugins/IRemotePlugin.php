<?php
namespace DeepQueue\Base\Plugins;


use DeepQueue\Base\Connector\Remote\IRemoteQueueProvider;
use DeepQueue\Base\IDeepQueueConfig;
use DeepQueue\Base\IMetaData;
use DeepQueue\Base\Plugins\RemoteElements\IMetaDataDAO;


interface IRemotePlugin extends IRemoteQueueProvider
{
	public function getMetaData(): IMetaData;
	
	public function setConfig(IDeepQueueConfig $config): void;
}