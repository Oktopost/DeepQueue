<?php
namespace DeepQueue\Plugins\Connectors\InMemoryConnector;


use DeepQueue\Base\Plugins\ConnectorElements\IQueueManager;
use DeepQueue\Scope;
use DeepQueue\Base\IMetaData;
use DeepQueue\Base\IQueueObject;
use DeepQueue\Base\IDeepQueueConfig;
use DeepQueue\Base\Queue\Remote\IRemoteQueue;
use DeepQueue\Plugins\Connectors\InMemoryConnector\Base\IInMemoryConnector;
use DeepQueue\Plugins\Connectors\InMemoryConnector\Base\IInMemoryQueueStorage;
use DeepQueue\Plugins\Connectors\InMemoryConnector\Queue\InMemoryQueue;
use DeepQueue\Plugins\Connectors\InMemoryConnector\Manager\InMemoryQueueManager;


class InMemoryConnector implements IInMemoryConnector
{
	/** @var IDeepQueueConfig */
	private $deepConfig = null;
	
	/** @var IInMemoryQueueStorage */
	private $storage;
	
	/** @var InMemoryQueueManager */
	private $manager;
	
	/** @var bool */
	private $isErrorsEnabled;
	
	
	public function __construct($enableErrorThrowing = false)
	{
		$this->storage = Scope::skeleton(IInMemoryQueueStorage::class);
		$this->manager = new InMemoryQueueManager();
		
		$this->isErrorsEnabled = $enableErrorThrowing;
	}
	

	public function setDeepConfig(IDeepQueueConfig $config): void
	{
		$this->deepConfig = $config;
	}
	
	public function manager(string $queueName): IQueueManager
	{
		$this->manager->setQueueName($queueName);
		return $this->manager;
	}

	public function getQueue(string $name): IRemoteQueue
	{
		return new InMemoryQueue($name, $this->deepConfig->serializer(), 
			$this->deepConfig->logger(), $this->isErrorsEnabled);
	}
}