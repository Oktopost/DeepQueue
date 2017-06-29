<?php
namespace DeepQueue\Plugins\InMemoryConnector;


use DeepQueue\Scope;
use DeepQueue\Base\IMetaData;
use DeepQueue\Base\IQueueObject;
use DeepQueue\Base\IDeepQueueConfig;
use DeepQueue\Base\Queue\Remote\IRemoteQueue;
use DeepQueue\Plugins\InMemoryConnector\Base\IInMemoryConnector;
use DeepQueue\Plugins\InMemoryConnector\Base\IInMemoryQueueStorage;
use DeepQueue\Plugins\InMemoryConnector\Queue\InMemoryQueue;
use DeepQueue\Plugins\InMemoryConnector\Manager\InMemoryQueueManager;


class InMemoryConnector implements IInMemoryConnector
{
	/** @var IDeepQueueConfig */
	private $deepConfig = null;
	
	/** @var IInMemoryQueueStorage */
	private $storage;
	
	/** @var bool */
	private $isErrorsEnabled;
	
	
	public function __construct($enableErrorThrowing = false)
	{
		$this->storage = Scope::skeleton(IInMemoryQueueStorage::class);
		$this->isErrorsEnabled = $enableErrorThrowing;
	}
	

	public function setDeepConfig(IDeepQueueConfig $config): void
	{
		$this->deepConfig = $config;
	}

	public function getMetaData(IQueueObject $queueObject): IMetaData
	{
		$manager = new InMemoryQueueManager($queueObject, $this->isErrorsEnabled);
		
		return $manager->getMetadata();
	}

	public function getQueue(string $name): IRemoteQueue
	{
		return new InMemoryQueue($name, $this->deepConfig->serializer(), $this->isErrorsEnabled);
	}
}