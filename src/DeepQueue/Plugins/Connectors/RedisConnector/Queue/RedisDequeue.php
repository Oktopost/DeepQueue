<?php
namespace DeepQueue\Plugins\Connectors\RedisConnector\Queue;


use DeepQueue\Utils\TimeGenerator;
use DeepQueue\Plugins\Connectors\RedisConnector\Base\IRedisDequeue;
use DeepQueue\Plugins\Connectors\RedisConnector\Base\IRedisQueueDAO;
use DeepQueue\Plugins\Connectors\RedisConnector\Helper\RedisNameBuilder;


class RedisDequeue implements IRedisDequeue
{	
	/** @var IRedisQueueDAO */
	private $dao;
	
	/** @var string */
	private $name;
	
	/** @var int */
	private $startTime = null;
	
	
	private function getOffset(array $element, float $depth = 0.0): float
	{
		$delayTime = reset($element);

		return max($delayTime - TimeGenerator::getMs($depth * -1), 0);
	}
	
	private function getWaitingTime(array $result, int $waitingSeconds): int 
	{
		$lastTime = round($this->getOffset($result) / 1000);	
		
		return ($lastTime > 0 && $lastTime < $waitingSeconds) ? $lastTime : $waitingSeconds;
	}
	
	private function getFirstDelayedWaitSeconds(int $waitingSeconds): int 
	{
		$firstDelayed = $this->dao->getFirstDelayed($this->name);
		
		return $firstDelayed ? $this->getWaitingTime($firstDelayed, $waitingSeconds) : $waitingSeconds;
	}

	private function getBufferReadyWaitSeconds(int $waitingSeconds, float $bufferDelay, int $size): int
	{
		$firstDelayed = $this->dao->getFirstDelayed($this->name);
		
		$bufferReady = $firstDelayed ? $this->getOffset($firstDelayed, $bufferDelay) : PHP_INT_MAX;
		
		$packageReady = PHP_INT_MAX;
		
		if ($size > 0)
		{
			$lastBulkElement = $this->dao->getDelayedElementByIndex($this->name, $size - 1);

			$packageReady = $lastBulkElement ? $this->getOffset($lastBulkElement, 0) : PHP_INT_MAX;
		}
		
		$closestDelay = min($bufferReady, $packageReady);
		
		return min($waitingSeconds, round($closestDelay / 1000));
	}
	
	private function decreaseWaiting(float $bufferDelay, int $size): int 
	{
		$timeLeft = ($this->startTime - TimeGenerator::getMs()) / 1000;

		$timeLeft = $timeLeft >= 0 ? round($timeLeft) : -1;
		
		if ($timeLeft <= 1) return $timeLeft;
		
		if (!$bufferDelay)
		{
			$timeToWait = $this->getFirstDelayedWaitSeconds($timeLeft);
		}
		else
		{
			$timeToWait = $this->getBufferReadyWaitSeconds($timeLeft, $bufferDelay, $size);
		}

		return $timeToWait >= 0 ? $timeToWait : -1;
	}
	
	
	public function __construct(IRedisQueueDAO $dao, string $queueName)
	{
		$this->dao = $dao;
		$this->name = $queueName;
	}

	
	public function dequeue(int $count = 1, int $waitSeconds, float $bufferDelay = 0.0, int $size = 0): array 
	{
		if (!$this->startTime)
		{
			$this->startTime = TimeGenerator::getMs($waitSeconds);
		}
				
		$this->dao->popDelayed($this->name, $bufferDelay, $size);

		$waitSeconds = $this->decreaseWaiting($bufferDelay, $size);

		$initialKey = $this->dao->dequeueInitialKey($this->name, $waitSeconds);

		if ($initialKey == RedisNameBuilder::getZeroKey() || (!$initialKey && $waitSeconds >= 0))
		{
			return $this->dequeue($count, $waitSeconds, $bufferDelay, $size);
		}
		
		if (!$initialKey)
		{
			return [];
		}
		
		return $this->dao->dequeueAll($this->name, $count - 1, $initialKey);
	}
}