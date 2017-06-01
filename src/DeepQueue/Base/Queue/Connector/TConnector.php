<?php
namespace DeepQueue\Base\Queue\Connector;


use DeepQueue\Base\Queue\IQueue;
use DeepQueue\Base\Queue\IDequeue;
use DeepQueue\Base\Queue\IEnqueue;


/**
 * @mixin IConnector
 */
trait TConnector
{
	public abstract function getStream(string $name): IQueue;
	
	
	public function getEnqueue(string $name): IEnqueue
	{
		return $this->getStream($name);
	}
	
	public function getDequeue(string $name): IDequeue
	{
		return $this->getStream($name);
	}
}