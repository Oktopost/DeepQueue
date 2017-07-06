<?php
namespace DeepQueue\Plugins\Connectors\InMemoryConnector\Manager;


use DeepQueue\Scope;
use DeepQueue\Base\IMetaData;
use DeepQueue\Base\IQueueObject;
use DeepQueue\Manager\MetaData;
use DeepQueue\Plugins\Connectors\InMemoryConnector\Base\IInMemoryQueueManager;
use DeepQueue\Plugins\Connectors\InMemoryConnector\Base\IInMemoryQueueDAO;


class InMemoryQueueManager implements IInMemoryQueueManager
{
	/** @var IQueueObject */
	private $queueObject;
	
	/** @var IInMemoryQueueDAO */
	private $dao;

	
	public function __construct(IQueueObject $queueObject)
	{
		$this->queueObject = $queueObject;
		$this->dao = Scope::skeleton(IInMemoryQueueDAO::class);
	}


	public function getMetadata(): IMetaData
	{
		$enqueued = $this->dao->countEnqueued($this->queueObject->Name);
		
		$metaData = new MetaData();
		$metaData->Enqueued = $enqueued;
		$metaData->Delayed = 0;
		
		return $metaData;
	}
}