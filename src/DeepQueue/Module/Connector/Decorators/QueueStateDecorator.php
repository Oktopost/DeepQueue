<?php
namespace DeepQueue\Module\Connector\Decorators;


use DeepQueue\Payload;
use DeepQueue\Workload;
use DeepQueue\Enums\QueueState;
use DeepQueue\Base\Connector\Decorator\IRemoteQueueDecorator;


class QueueStateDecorator implements IRemoteQueueDecorator 
{
	use \DeepQueue\Module\Connector\Decorators\Base\TRemoteQueueDecorator;
	
	
	private $sleepTimeMS;
	
	
	public function __construct(int $defaultSleepTimeMS = 10 * 1000)
	{
		$this->sleepTimeMS = $defaultSleepTimeMS;
	}
	
	
	/**
	 * @return Workload[]
	 */
	public function dequeueWorkload(int $count = 1, ?float $waitSeconds = null): array
	{
		$queue = $this->requireQueue();

		$remainingMS = (int)(($waitSeconds ?: 0) * 1000);
		
		while ($queue->State != QueueState::RUNNING)
		{
			if ($remainingMS <= 0)
				return [];
			
			$sleepMS = min($remainingMS, $this->sleepTimeMS);
			$remainingMS -= $sleepMS;
			
			usleep($sleepMS * 1000);
			
			$queue = $this->requireQueue();
		}
		
		return $this->getRemoteQueue()->dequeueWorkload($count, $remainingMS / 1000);
	}
	
	/**
	 * @param Payload[] $payload
	 * @return ?string[] IDs for each payload
	 */
	public function enqueue(array $payload): array
	{
		$queue = $this->requireQueue();
		
		if ($queue->State == QueueState::STOPPED)
		{
			return array_pad([], count($payload), null);
		}
		
		return $this->getRemoteQueue()->enqueue($payload);
	}
}