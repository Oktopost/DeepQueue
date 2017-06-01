<?php
namespace DeepQueue\Base\Connector\Decorator;


use DeepQueue\Base\Connector\Loader\IQueueLoader;
use DeepQueue\Base\Queue\Remote\IRemoteQueue;


interface IRemoteQueueDecorator extends IRemoteQueue 
{
	public function setRemoteQueue(IRemoteQueue $queue): void;
	public function setQueueLoader(IQueueLoader $loader): void;
}