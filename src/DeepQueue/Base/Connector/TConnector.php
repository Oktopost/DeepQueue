<?php
namespace DeepQueue\Base\Connector;


use DeepQueue\Base\Stream\IDequeue;
use DeepQueue\Base\Stream\IEnqueue;
use DeepQueue\Base\Stream\IQueueStream;


/**
 * @mixin IConnector
 */
trait TConnector
{
	public abstract function getStream(string $name): IQueueStream;
	
	public function getEnqueue(string $name): IEnqueue
	{
		return $this->getStream($name);
	}
	
	public function getDequeue(string $name): IDequeue
	{
		return $this->getStream($name);
	}
}