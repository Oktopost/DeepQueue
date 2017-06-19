<?php
namespace DeepQueue\Module\Connector\LoaderDecorators;


use DeepQueue\Base\IQueueObject;
use DeepQueue\Base\Loader\IQueueObjectLoader;
use DeepQueue\Base\Loader\Decorator\IQueueLoaderDecorator;


class CachedLoaderDecorator implements IQueueLoaderDecorator
{
	private $timeoutSec;
	private $cachedTime = -1;
	
	/** @var IQueueObjectLoader */
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
	
	
	public function __construct(float $timeoutSec = 5.0)
	{
		$this->timeoutSec = $timeoutSec;
	}

	public function setChildLoader(IQueueObjectLoader $loader): void
	{
		$this->loader = $loader;
	}


	public function load(): ?IQueueObject
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