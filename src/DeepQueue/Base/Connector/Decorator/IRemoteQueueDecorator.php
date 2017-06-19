<?php
namespace DeepQueue\Base\Connector\Decorator;


use DeepQueue\Base\Loader\IQueueObjectLoader;
use DeepQueue\Base\Queue\Remote\IRemoteQueue;


interface IRemoteQueueDecorator extends IRemoteQueue 
{
	public function setRemoteQueue(IRemoteQueue $queue): void;
	public function setQueueLoader(IQueueObjectLoader $loader): void;
}