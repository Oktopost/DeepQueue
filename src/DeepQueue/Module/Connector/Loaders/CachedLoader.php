<?php
namespace DeepQueue\Module\Connector;


use DeepQueue\Base\IQueueObject;
use DeepQueue\Base\Connector\Loader\IQueueLoader;


class CachedLoader implements IQueueLoader
{
	private $timeoutSec;
	private $cachedTime = -1;
	
	/** @var IQueueLoader */
	private $loader;
	
	/** @var IQueueObject|null */
	private $queue = null;
	
	
	private function checkIfExpired()
	{
		if (time() - $this->cachedTime > $this->timeoutSec)
		{
			$this->queue = null;
		}
	}
	
	
	public function __construct(IQueueLoader $child, float $timeoutSec = 5.0)
	{
		$this->loader = $child;
		$this->timeoutSec = $timeoutSec;
	}
	
	
	public function load(): IQueueObject
	{
		$this->checkIfExpired();
		
		if (!$this->queue)
		{
			$this->cachedTime = time();
			$this->queue = $this->loader->load();
		}
		
		return $this->queue;
	}
	
	/**
	 * Exception is thrown if queue does not exist.
	 * @return IQueueObject
	 */
	public function require (): IQueueObject
	{
		$this->checkIfExpired();
		
		if (!$this->queue)
		{
			$this->cachedTime = time();
			$this->queue = $this->loader->require();
		}
		
		return $this->queue;
	}
}