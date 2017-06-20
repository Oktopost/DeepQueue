<?php
namespace DeepQueue\Base\Plugins;


use DeepQueue\Base\Connector\Remote\IRemoteQueueProvider;
use DeepQueue\Base\Plugins\RemoteElements\IMetaDataDAO;


interface IRemotePlugin extends IRemoteQueueProvider
{
	public function getMetaDataDAO(): IMetaDataDAO;
}