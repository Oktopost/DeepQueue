<?php
namespace DeepQueue\Plugins\MySQLConnector\Manager;


use DeepQueue\Base\IMetaData;
use DeepQueue\Base\IQueueObject;
use DeepQueue\Manager\MetaData;
use DeepQueue\Plugins\MySQLConnector\Base\DAO\IMySQLQueueDAO;
use DeepQueue\Plugins\MySQLConnector\Base\IMySQLQueueManager;


class MySQLQueueManager implements IMySQLQueueManager
{
	/** @var IQueueObject */
	private $queueObject;
	
	/** @var IMySQLQueueDAO */
	private $dao;
	
	
	public function __construct(IQueueObject $queueObject, IMySQLQueueDAO $dao)
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