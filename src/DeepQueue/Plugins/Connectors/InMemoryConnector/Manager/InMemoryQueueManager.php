<?php
namespace DeepQueue\Plugins\Connectors\InMemoryConnector\Manager;


use DeepQueue\Scope;
use DeepQueue\Base\IMetaData;
use DeepQueue\Manager\MetaData;
use DeepQueue\Plugins\Connectors\InMemoryConnector\Base\IInMemoryQueueManager;
use DeepQueue\Plugins\Connectors\InMemoryConnector\Base\IInMemoryQueueDAO;
use DeepQueue\Exceptions\InvalidUsageException;


class InMemoryQueueManager implements IInMemoryQueueManager
{
	private $queueName = null;
	
	/** @var IInMemoryQueueDAO */
	private $dao;

	
	private function checkConfiguration(): void
	{
		if (!$this->queueName)
		{
			throw new InvalidUsageException('Setup Queue name before using queue manager');
		}
	}
	
	
	public function __construct(?IInMemoryQueueDAO $dao = null)
	{
		$this->dao = $dao ? $dao : Scope::skeleton(IInMemoryQueueDAO::class);
	}
	
	
	public function setQueueName(string $queueName): void
	{
		$this->queueName = $queueName;
	}

	public function getMetaData(): IMetaData
	{
		$this->checkConfiguration();
		
		$enqueued = $this->dao->countEnqueued($this->queueName);
		
		$metaData = new MetaData();
		$metaData->Enqueued = $enqueued;
		$metaData->Delayed = 0;
		
		return $metaData;
	}
	
	public function clearQueue(): void
	{
		$this->checkConfiguration();
		$this->dao->clearQueue($this->queueName);
	}

	public function getWaitingTime(float $secondsDepth = 0.0, int $bulkSize = 0): ?float
	{
		$this->checkConfiguration();
		return $this->dao->countEnqueued($this->queueName) > 0 ? 0 : null;
	}

	public function flushDelayed(): void
	{
		return;
	}
}