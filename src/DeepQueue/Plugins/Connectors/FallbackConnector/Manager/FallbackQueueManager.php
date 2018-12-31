<?php
namespace DeepQueue\Plugins\Connectors\FallbackConnector\Manager;


use DeepQueue\Base\IMetaData;
use DeepQueue\Base\Plugins\ConnectorElements\IQueueManager;
use DeepQueue\Plugins\Logger\Base\ILogger;


class FallbackQueueManager implements IQueueManager
{
	/** @var string */
	private $queueName;
	
	/** @var IQueueManager */
	private $main;
	
	/** @var IQueueManager */
	private $fallback;
	
	/** @var ILogger */
	private $logger;
	
	
	private function log(\Throwable $e, string $operation, ?array $data = null): void
	{
		$message = "Fallback queue manager error: Failed to {$operation} data for {$this->queueName} queue.";
		$this->logger->logException($e, $message, $data, $this->queueName);
	}
	
	
	public function __construct(IQueueManager $main, IQueueManager $fallback, ILogger $logger)
	{
		$this->main = $main;
		$this->fallback = $fallback;
		$this->logger = $logger;
	}


	public function setQueueName(string $queueName): void
	{
		$this->queueName = $queueName;
		$this->main->setQueueName($queueName);
		$this->fallback->setQueueName($queueName);
	}

	public function getMetaData(): IMetaData
	{
		try
		{
			return $this->main->getMetaData();
		}
		catch (\Throwable $e)
		{
			$this->log($e, 'getMetaData');
			return $this->fallback->getMetaData();
		}
	}

	public function getWaitingTime(float $secondsDepth = 0.0, int $bulkSize = 0): ?float
	{
		try
		{
			return $this->main->getWaitingTime($secondsDepth, $bulkSize);
		}
		catch (\Throwable $e)
		{
			$this->log($e, 'getWaitingTime', [
				'secondsDepth' => $secondsDepth, 
				'bulkSize' => $bulkSize
			]);
			
			return $this->fallback->getWaitingTime($secondsDepth, $bulkSize);
		}
	}

	public function flushDelayed(): void
	{
		try
		{
			$this->main->flushDelayed();
			$this->fallback->flushDelayed();
		}
		catch (\Throwable $e)
		{
			$this->log($e, 'flushDelayed');
		}
	}

	public function clearQueue(): void
	{
		try
		{
			$this->main->clearQueue();
			$this->fallback->clearQueue();
		}
		catch (\Throwable $e)
		{
			$this->log($e, 'clearQueue');
		}
	}
}