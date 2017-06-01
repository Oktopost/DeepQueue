<?php
namespace DeepQueue\Module\Connector\Decorators\Base;


use DeepQueue\Base\IQueueObject;
use DeepQueue\Base\Queue\Remote\IRemoteQueue;
use DeepQueue\Base\Connector\Loader\IQueueLoader;


trait TRemoteQueueDecorator
{
	/** @var IRemoteQueue */
	private $_queue;
	
	/** @var IQueueLoader */
	private $_loader;
	
	
	private function getRemoteQueue(): IRemoteQueue
	{
		return $this->_queue;
	}
	
	private function getQueue(): ?IQueueObject
	{
		return $this->_loader->load();
	}
	
	private function requireQueue(): IQueueObject
	{
		return $this->_loader->require();
	}
	
	
	public function setRemoteQueue(IRemoteQueue $queue): void
	{
		$this->_queue = $queue;
	}
	
	public function setQueueLoader(IQueueLoader $loader): void
	{
		$this->_loader = $loader;
	}
}