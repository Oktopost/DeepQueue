<?php
namespace DeepQueue\Plugins\InMemoryRemote;


use DeepQueue\Base\IMetaData;
use DeepQueue\Base\IDeepQueueConfig;
use DeepQueue\Base\Queue\Remote\IRemoteQueue;
use DeepQueue\Plugins\InMemoryRemote\Base\IInMemoryRemote;
use DeepQueue\Plugins\InMemoryRemote\Queue\InMemoryRemoteQueue;


class InMemoryRemote implements IInMemoryRemote
{
	/** @var IDeepQueueConfig */
	private $config = null;
	
	
	public function setConfig(IDeepQueueConfig $config): void
	{
		$this->config = $config;
	}

	public function getMetaData(): IMetaData
	{
//		$manager = new InMemoryQueueManager();
//		
//		return $manager->getMetadata();
	}

	public function getQueue(string $name): IRemoteQueue
	{
		return new InMemoryRemoteQueue($name, $this->config->converter());
	}

}