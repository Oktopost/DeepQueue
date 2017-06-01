<?php
namespace DeepQueue\Base\Plugins;


use DeepQueue\Base\Queue\Remote\IRemoteQueue;
use DeepQueue\Base\Plugins\RemoteElements\IMetaDataDAO;


interface IRemotePlugin
{
	public function getMetaDataDAO(): IMetaDataDAO;
	public function getQueue(string $queueName): IRemoteQueue;
}