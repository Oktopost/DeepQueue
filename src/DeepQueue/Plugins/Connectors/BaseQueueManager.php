<?php
namespace DeepQueue\Plugins\Connectors;


use DeepQueue\Base\IMetaData;
use DeepQueue\Base\Plugins\ConnectorElements\IQueueManager;
use DeepQueue\Base\Plugins\ConnectorElements\IQueueManagerDAO;
use DeepQueue\Manager\MetaData;
use DeepQueue\Utils\TimeGenerator;
use DeepQueue\Exceptions\InvalidUsageException;


class BaseQueueManager implements IQueueManager
{
	/** @var IQueueManagerDAO */
	private $dao = null;
	
	private $queueName = null;
	
	
	private function checkConfiguration(): void
	{
		if (!$this->queueName || !$this->dao)
		{
			throw new InvalidUsageException('Setup Queue name and DAO before using queue manager');
		}
	}
	
	private function getOffset(array $element, float $depth = 0.0): float
	{
		$delayTime = reset($element);

		return max($delayTime - TimeGenerator::getMs($depth * -1), 0);
	}
	
	private function getBufferDelay(string $queueName, float $depth = 0.0): float
	{
		$firstDelayedElement = $this->dao->getFirstDelayed($queueName);
		
		return $firstDelayedElement ? $this->getOffset($firstDelayedElement, $depth) : -1;
	}
	
	private function getBulkFilledDelay(string $queueName, int $bulkSize): float
	{
		$readyBulkSize = $this->dao->countDelayedReadyToDequeue($queueName);

		if ($readyBulkSize >= $bulkSize)
		{
			return 0;
		}
		
		$lastBulkElement = $this->dao->getDelayedElementByIndex($queueName, $bulkSize - 1);
		
		return $lastBulkElement ? $this->getOffset($lastBulkElement, 0) : -1;
	}
	
	private function isNotDelayedExists(string $queueName): bool
	{
		return ($this->dao->countNotDelayed($queueName) > 0);
	}
	
	
	public function setDAO(IQueueManagerDAO $dao): void
	{
		$this->dao = $dao;
	}
	
	public function setQueueName(string $queueName): void
	{
		$this->queueName = $queueName;
	}

	public function getMetaData(): IMetaData
	{
		$this->checkConfiguration();
		
		$metaData = new MetaData();
		$metaData->Enqueued = $this->dao->countEnqueued($this->queueName);
		$metaData->Delayed = $this->dao->countDelayed($this->queueName);
		
		return $metaData;
	}

	public function getWaitingTime(float $secondsDepth = 0.0, int $bulkSize = 0): ?float
	{
		$this->checkConfiguration();
		
		if ($this->isNotDelayedExists($this->queueName))
		{
			return 0;
		}
		
		$delayMs = $this->getBufferDelay($this->queueName, $secondsDepth);
	
		if ($delayMs < 0)
		{
			return null;
		}
		
		if ($bulkSize > 0)
		{
			$bulkDelayMs = $this->getBulkFilledDelay($this->queueName, $bulkSize);
			
			if ($bulkDelayMs >= 0)
			{
				$delayMs = min($delayMs, $bulkDelayMs);
			}
		}
		
		return max($delayMs / 1000, 0);
	}

	public function flushDelayed(): void
	{
		$this->checkConfiguration();
		$this->dao->flushDelayed($this->queueName);
	}

	public function clearQueue(): void
	{
		$this->checkConfiguration();
		$this->dao->clearQueue($this->queueName);
	}
}