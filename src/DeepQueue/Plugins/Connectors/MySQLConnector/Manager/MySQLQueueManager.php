<?php
namespace DeepQueue\Plugins\Connectors\MySQLConnector\Manager;


use DeepQueue\Base\IMetaData;
use DeepQueue\Manager\MetaData;
use DeepQueue\Exceptions\InvalidUsageException;
use DeepQueue\Plugins\Connectors\MySQLConnector\Base\DAO\IMySQLQueueDAO;
use DeepQueue\Plugins\Connectors\MySQLConnector\Base\IMySQLQueueManager;


class MySQLQueueManager implements IMySQLQueueManager
{
	private $queueName = null;
	
	/** @var IMySQLQueueDAO */
	private $dao;
	
	
	public function __construct(IMySQLQueueDAO $dao)
	{
		$this->dao = $dao;
	}
	
	
	public function setQueueName(string $queueName): void
	{
		$this->queueName = $queueName;
	}

	public function getMetaData(): IMetaData
	{
		$metaData = new MetaData();
		
		if (!$this->queueName)
			return $metaData;
		
		$enqueued = $this->dao->countEnqueued($this->queueName);
		$delayed = $this->dao->countDelayed($this->queueName);
		
		$metaData->Enqueued = $enqueued;
		$metaData->Delayed = $delayed;
		
		return $metaData;
	}
	
	public function clearQueue(): void
	{
		if (!$this->queueName)
		{
			throw new InvalidUsageException('It is required to set queue name in manager before clearing it');
		}
		
		$this->dao->clearQueue($this->queueName);
	}
}