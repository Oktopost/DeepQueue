<?php
namespace DeepQueue\Plugins\Connectors\InMemoryConnector\Manager;


use DeepQueue\Scope;
use DeepQueue\Base\IMetaData;
use DeepQueue\Manager\MetaData;
use DeepQueue\Plugins\Connectors\InMemoryConnector\Base\IInMemoryQueueManager;
use DeepQueue\Plugins\Connectors\InMemoryConnector\Base\IInMemoryQueueDAO;


class InMemoryQueueManager implements IInMemoryQueueManager
{
	private $queueName = null;
	
	/** @var IInMemoryQueueDAO */
	private $dao;

	
	public function __construct()
	{
		$this->dao = Scope::skeleton(IInMemoryQueueDAO::class);
	}
	
	
	public function setQueueName(string $queueName): void
	{
		$this->queueName = $queueName;
	}

	public function getMetaData(): IMetaData
	{
		$enqueued = $this->dao->countEnqueued($this->queueName);
		
		$metaData = new MetaData();
		$metaData->Enqueued = $enqueued;
		$metaData->Delayed = 0;
		
		return $metaData;
	}
	
	public function clearQueue(): void
	{
		$this->dao->clearQueue($this->queueName);
	}
}