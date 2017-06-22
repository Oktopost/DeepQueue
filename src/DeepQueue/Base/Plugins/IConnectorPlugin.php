<?php
namespace DeepQueue\Base\Plugins;


use DeepQueue\Base\IMetaData;
use DeepQueue\Base\IQueueObject;
use DeepQueue\Base\Connector\Remote\IRemoteQueueProvider;


interface IConnectorPlugin extends IRemoteQueueProvider, IConfigurable
{
	public function getMetaData(IQueueObject $queueObject): IMetaData;
}