<?php
namespace DeepQueue;


use DeepQueue\Base\IMetaData;
use DeepQueue\Base\IQueueObject;
use DeepQueue\Base\IDeepQueueConfig;
use DeepQueue\Base\Plugins\ConnectorElements\IQueueManager;
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
		return new Queue($name, $remoteQueue, $this->config->logger());
	}
	
	public function getQueueObject(string $name): ?IQueueObject
	{
		$loader = $this->config->getQueueLoader($name);
		return $loader->load();
	}

	/**
	 * @deprecated
	 */
	public function getMetaData(string $name): ?IMetaData
	{
		return $this->config()->connector()->manager($name)->getMetaData();
	}
	
	public function manager(string $name): IQueueManager
	{
		return $this->config()->connector()->manager($name);
	}
}