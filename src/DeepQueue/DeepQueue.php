<?php
namespace DeepQueue;


use DeepQueue\Base\IDeepQueueConfig;
use DeepQueue\Base\IQueueObject;
use DeepQueue\Base\Queue\IQueue;
use DeepQueue\Module\Queue\Queue;


class DeepQueue
{
	/** @var DeepQueueConfig */
	private $config;
	
	
	public function __construct()
	{
		$this->config = new DeepQueueConfig();
	}
	
	
	public function config(): IDeepQueueConfig
	{
		return $this->config;
	}
	
	public function get(string $name): IQueue
	{
		$remoteQueue = $this->config->getConnectorProvider()->getRemoteQueue($name);
		return new Queue($remoteQueue);
	}
	
	public function getQueueObject(string $name): ?IQueueObject
	{
		$loader = $this->config->getQueueLoader($name);
		return $loader->load();
	}
}