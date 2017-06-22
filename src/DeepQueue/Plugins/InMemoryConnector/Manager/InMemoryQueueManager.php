<?php
namespace DeepQueue\Plugins\InMemoryConnector\Manager;


use DeepQueue\Scope;
use DeepQueue\Base\IMetaData;
use DeepQueue\Base\IQueueObject;
use DeepQueue\Manager\MetaData;
use DeepQueue\Plugins\InMemoryConnector\Base\IInMemoryQueueManager;
use DeepQueue\Plugins\InMemoryConnector\Base\IInMemoryQueueConnector;


class InMemoryQueueManager implements IInMemoryQueueManager
{
	/** @var IQueueObject */
	private $queueObject;
	
	/** @var IInMemoryQueueConnector */
	private $connector;
	
	
	public function __construct(IQueueObject $queueObject)
	{
		$this->queueObject = $queueObject;
		$this->connector = Scope::skeleton(IInMemoryQueueConnector::class);
	}


	public function getMetadata(): IMetaData
	{
		$enqueued = $this->connector->countEnqueued($this->queueObject->Name);
		
		$metaData = new MetaData();
		$metaData->Enqueued = $enqueued;
		$metaData->Delayed = 0;
		
		return $metaData;
	}
}