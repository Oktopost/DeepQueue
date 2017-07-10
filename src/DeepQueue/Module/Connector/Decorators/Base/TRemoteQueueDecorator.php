<?php
namespace DeepQueue\Module\Connector\Decorators\Base;


use DeepQueue\Base\IQueueObject;
use DeepQueue\Base\Queue\Remote\IRemoteQueue;
use DeepQueue\Base\Loader\IQueueObjectLoader;


trait TRemoteQueueDecorator
{
	/** @var IRemoteQueue */
	private $_queue;
	
	/** @var IQueueObjectLoader */
	private $_loader;
	
	
	private function getRemoteQueue(): IRemoteQueue
	{
		return $this->_queue;
	}
	
	private function requireQueue(): IQueueObject
	{
		return $this->_loader->require();
	}
	
	
	public function setRemoteQueue(IRemoteQueue $queue): void
	{
		$this->_queue = $queue;
	}
	
	public function setQueueLoader(IQueueObjectLoader $loader): void
	{
		$this->_loader = $loader;
	}
}