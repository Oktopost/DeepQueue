<?php
namespace DeepQueue\Plugins\RedisConnector\Manager;


use DeepQueue\Base\IMetaData;
use DeepQueue\Base\IQueueObject;
use DeepQueue\Manager\MetaData;
use DeepQueue\Plugins\RedisConnector\Base\IRedisQueueDAO;
use DeepQueue\Plugins\RedisConnector\Base\IRedisQueueManager;


class RedisQueueManager implements IRedisQueueManager
{
	/** @var IQueueObject */
	private $queueObject;
	
	/** @var IRedisQueueDAO */
	private $dao;
	
	
	public function __construct(IQueueObject $queueObject, IRedisQueueDAO $dao)
	{
		$this->queueObject = $queueObject;
		$this->dao = $dao;
	}
	
	
	public function getMetadata(): IMetaData
	{
		$enqueued = $this->dao->countEnqueued($this->queueObject->Name);
		$delayed = $this->dao->countDelayed($this->queueObject->Name);
		
		$metaData = new MetaData();
		$metaData->Enqueued = $enqueued;
		$metaData->Delayed = $delayed;
		
		return $metaData;
	}
}