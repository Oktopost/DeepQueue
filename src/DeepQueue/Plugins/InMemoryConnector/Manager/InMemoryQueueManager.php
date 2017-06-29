<?php
namespace DeepQueue\Plugins\InMemoryConnector\Manager;


use DeepQueue\Scope;
use DeepQueue\Base\IMetaData;
use DeepQueue\Base\IQueueObject;
use DeepQueue\Manager\MetaData;
use DeepQueue\Plugins\InMemoryConnector\Base\IInMemoryQueueManager;
use DeepQueue\Plugins\InMemoryConnector\Base\IInMemoryQueueDAO;


class InMemoryQueueManager implements IInMemoryQueueManager
{
	/** @var IQueueObject */
	private $queueObject;
	
	/** @var IInMemoryQueueDAO */
	private $dao;
	
	/** @var bool */
	private $isErrorsEnabled;
	
	
	private function throwErrorWithRand(): void
	{
		$rand = (float)rand()/(float)getrandmax();
		if ($rand < 0.2)
		{
			throw new \Exception('Error for debug');
		}
	}
	
	
	public function __construct(IQueueObject $queueObject, $enableErrors = false)
	{
		$this->queueObject = $queueObject;
		$this->dao = Scope::skeleton(IInMemoryQueueDAO::class);
		$this->isErrorsEnabled = $enableErrors;
	}


	public function getMetadata(): IMetaData
	{
		if ($this->isErrorsEnabled)
		{
			$this->throwErrorWithRand();
		}
		
		$enqueued = $this->dao->countEnqueued($this->queueObject->Name);
		
		$metaData = new MetaData();
		$metaData->Enqueued = $enqueued;
		$metaData->Delayed = 0;
		
		return $metaData;
	}
}