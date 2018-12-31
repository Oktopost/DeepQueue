<?php
namespace DeepQueue\Plugins\Connectors\FallbackConnector;


use DeepQueue\Base\IDeepQueueConfig;
use DeepQueue\Base\Queue\Remote\IRemoteQueue;
use DeepQueue\Base\Plugins\IConnectorPlugin;
use DeepQueue\Base\Plugins\ConnectorElements\IQueueManager;
use DeepQueue\Plugins\Connectors\FallbackConnector\Manager\FallbackQueueManager;
use DeepQueue\Plugins\Connectors\FallbackConnector\Queue\FallbackQueue;
use DeepQueue\Plugins\Connectors\FallbackConnector\Base\IFallbackConnector;


class FallbackConnector implements IFallbackConnector
{
	/** @var IConnectorPlugin */
	private $main;
	
	/** @var IConnectorPlugin */
	private $fallback;
	
	/** @var IDeepQueueConfig|null */
	private $deepConfig = null;
	
	
	public function __construct(IConnectorPlugin $main, IConnectorPlugin $fallback)
	{
		$this->main = $main;
		$this->fallback = $fallback;
	}

	
	public function setDeepConfig(IDeepQueueConfig $config): void
	{
		$this->deepConfig = $config;
		
		$this->main->setDeepConfig($config);
		$this->fallback->setDeepConfig($config);
	}

	public function manager(string $queueName): IQueueManager
	{
		$mainManager = $this->main->manager($queueName);
		$fallbackManager = $this->fallback->manager($queueName);
		
		$manager = new FallbackQueueManager($mainManager, $fallbackManager, $this->deepConfig->logger());
		$manager->setQueueName($queueName);
		
		return $manager;
	}

	public function getQueue(string $name): IRemoteQueue
	{
		return new FallbackQueue($name, $this->main->getQueue($name), 
			$this->fallback->getQueue($name), $this->deepConfig->logger());
	}
}