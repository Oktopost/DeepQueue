<?php
namespace DeepQueue\Plugins\RedisConnector\Queue;


use DeepQueue\Utils\TimeGenerator;
use DeepQueue\Plugins\RedisConnector\Base\IRedisDequeue;
use DeepQueue\Plugins\RedisConnector\Base\IRedisQueueDAO;
use DeepQueue\Plugins\RedisConnector\Helper\RedisNameBuilder;


class RedisDequeue implements IRedisDequeue
{	
	/** @var IRedisQueueDAO */
	private $dao;
	
	/** @var string */
	private $name;
	
	/** @var int */
	private $startTime = null;
	
	
	private function getWaitingTime(array $result, int $waitingSeconds): int 
	{
		if ($waitingSeconds < 1 || empty($result))
		{
			return $waitingSeconds;
		}
		
		$lastTime = round(($result[array_keys($result)[0]] - TimeGenerator::getMs()) / 1000);	
		
		return ($lastTime > 0 && $lastTime < $waitingSeconds) ? $lastTime : $waitingSeconds;
	}
	
	private function getFirstDelayedWaitSeconds(int $waitingSeconds): int 
	{
		if ($waitingSeconds <= 0)
			return $waitingSeconds;
		
		$firstDelayed = $this->dao->getFirstDelayed($this->name);
		
		return $firstDelayed ? $this->getWaitingTime($firstDelayed, $waitingSeconds) : $waitingSeconds;
	}
	
	private function decreaseWaiting(): int 
	{
		$timeLeft = ($this->startTime - TimeGenerator::getMs()) / 1000;

		$timeLeft = $timeLeft >= 0 ? round($timeLeft) : -1;
				
		$timeToWait = $this->getFirstDelayedWaitSeconds($timeLeft);
		
		return $timeToWait >= 0 ? $timeToWait : -1;
	}
	
	
	public function __construct(IRedisQueueDAO $dao, string $queueName)
	{
		$this->dao = $dao;
		$this->name = $queueName;
	}

	
	public function dequeue(int $count = 1, int $waitSeconds): array 
	{
		if (!$this->startTime)
		{
			$this->startTime = TimeGenerator::getMs($waitSeconds);
		}
		
		$this->dao->popDelayed($this->name);

		$waitSeconds = $this->decreaseWaiting();

		$initialKey = $this->dao->dequeueInitialKey($this->name, $waitSeconds);

		if ($initialKey == RedisNameBuilder::getZeroKey() || (!$initialKey && $waitSeconds >= 0))
		{
			return $this->dequeue($count, $waitSeconds);
		}
		
		if (!$initialKey)
		{
			return [];
		}
		
		return $this->dao->dequeueAll($this->name, $count - 1, $initialKey);
	}
}