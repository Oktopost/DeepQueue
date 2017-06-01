<?php
namespace DeepQueue\Base\Plugins;


use DeepQueue\Base\Plugins\RemoteElements\IMetaDataDAO;
use DeepQueue\Base\Queue\Remote\IRemoteQueue;


interface IRemotePlugin
{
	public function getMetaDataDAO(): IMetaDataDAO;
	public function getQueue(string $queueName): IRemoteQueue;
}