<?php
namespace DeepQueue\Plugins\Connectors\FallbackConnector;


use DeepQueue\Base\IMetaData;
use DeepQueue\Base\IQueueObject;
use DeepQueue\Base\IDeepQueueConfig;
use DeepQueue\Base\Plugins\ConnectorElements\IQueueManager;
use DeepQueue\Base\Plugins\IConnectorPlugin;
use DeepQueue\Base\Queue\Remote\IRemoteQueue;
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
		try
		{
			return $this->main->manager($queueName);
		}
		catch (\Throwable $e)
		{
			$this->deepConfig->logger()->logException($e, 
				"Failed to load manager for {$queueName} queue object", [], $queueName);
			
			return $this->fallback->manager($queueName);
		}
	}

	public function getQueue(string $name): IRemoteQueue
	{
		return new FallbackQueue($name, $this->main->getQueue($name), 
			$this->fallback->getQueue($name), $this->deepConfig->logger());
	}
}