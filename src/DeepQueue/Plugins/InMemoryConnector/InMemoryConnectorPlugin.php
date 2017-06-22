<?php
namespace DeepQueue\Plugins\InMemoryConnector;


use DeepQueue\Base\IMetaData;
use DeepQueue\Base\IQueueObject;
use DeepQueue\Base\IDeepQueueConfig;
use DeepQueue\Base\Queue\Remote\IRemoteQueue;
use DeepQueue\Plugins\InMemoryConnector\Base\IInMemoryConnectorPlugin;
use DeepQueue\Plugins\InMemoryConnector\Queue\InMemoryQueue;
use DeepQueue\Plugins\InMemoryConnector\Manager\InMemoryQueueManager;


class InMemoryConnectorPlugin implements IInMemoryConnectorPlugin
{
	/** @var IDeepQueueConfig */
	private $config = null;
	
	
	public function setConfig(IDeepQueueConfig $config): void
	{
		$this->config = $config;
	}

	public function getMetaData(IQueueObject $queueObject): IMetaData
	{
		$manager = new InMemoryQueueManager($queueObject);
		
		return $manager->getMetadata();
	}

	public function getQueue(string $name): IRemoteQueue
	{
		return new InMemoryQueue($name, $this->config->converter());
	}
}