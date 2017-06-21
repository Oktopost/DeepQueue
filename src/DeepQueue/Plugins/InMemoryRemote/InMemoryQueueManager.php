<?php
namespace DeepQueue\Plugins\InMemoryRemote;


use DeepQueue\Base\IMetaData;
use DeepQueue\Manager\MetaData;
use DeepQueue\Plugins\InMemoryRemote\Base\IInMemoryQueueConnector;
use DeepQueue\Scope;

class InMemoryQueueManager
{
	private $name;
	
	/** @var IInMemoryQueueConnector */
	private $connector;
	
	
	public function __construct($name)
	{
		$this->name = $name;
		$this->connector = Scope::skeleton(IInMemoryQueueConnector::class);
	}


	public function getMetadata(): IMetaData
	{
		$enqueued = $this->connector->countEnqueued($this->name);
		
		$metaData = new MetaData();
		$metaData->Enqueued = $enqueued;
		$metaData->Delayed = 0;
		
		return $metaData;
	}
}