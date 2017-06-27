<?php
namespace DeepQueue\Plugins\RedisConnector\Queue;


use DeepQueue\Utils\TimeGenerator;
use DeepQueue\Plugins\RedisConnector\Base\IRedisQueueDAO;


class RedisDequeue
{
	private const ZEROKEY = '000-000-000';
	
	
	/** @var IRedisQueueDAO */
	private $dao;
	
	private $name;
	
	private $waitSeconds;
	
	
	private function dequeueWithWaiting(int $count): array
	{
		$endTime = TimeGenerator::getMs($this->waitSeconds);
		$nowTime = TimeGenerator::getMs();
		
		$payloads = [];

		while ($nowTime < $endTime)
		{
			$key = $this->dao->dequeueInitialKey($this->name, $this->waitSeconds);
			
			if ($key == self::ZEROKEY)
			{
				$this->dao->popDelayed($this->name);
				
				$this->waitSeconds = $this->getDelayedWaitSeconds($this->waitSeconds);
				
				$endTime = TimeGenerator::getMs($this->waitSeconds);
			}

			if ($key)
			{
				$payloads = $this->dao->dequeueAll($this->name, $count - 1, $key);
			}
			
			if ($payloads)
			{
				break;
			}
			
			$nowTime = TimeGenerator::getMs();
		}
		
		return $payloads;
	}
	
	private function dequeueNow(int $count): array
	{
		$key = $this->dao->dequeueInitialKey($this->name, $this->waitSeconds);
		return $this->dao->dequeueAll($this->name, $count - 1, $key);
	}
	
	private function getWaitingTime(array $result, int $waitingSeconds): int 
	{
		if ($waitingSeconds < 1 || empty($result))
		{
			return $waitingSeconds;
		}
		
		$lastTime = round(($result[array_keys($result)[0]] - TimeGenerator::getMs()) / 1000);	
		
		return $lastTime < $waitingSeconds ? $lastTime : $waitingSeconds;
	}
	
	private function getDelayedWaitSeconds(int $waitingSeconds): int 
	{
		$firstDelayed = $this->dao->getFirstDelayed($this->name);
		
		return $firstDelayed ? $this->getWaitingTime($firstDelayed, $waitingSeconds) : $waitingSeconds;
	}
	
	
	public function __construct(IRedisQueueDAO $dao, string $queueName, int $waitSeconds)
	{
		$this->dao = $dao;
		$this->name = $queueName;
		$this->waitSeconds = $waitSeconds;
	}
	
	
	public function dequeue(int $count = 1)
	{
		$this->dao->popDelayed($this->name);
		
		if ($this->waitSeconds > 0)
		{
			$this->waitSeconds = $this->getDelayedWaitSeconds($this->waitSeconds);
			
			return $this->dequeueWithWaiting($count);
		}
		
		return $this->dequeueNow($count);
	}
}