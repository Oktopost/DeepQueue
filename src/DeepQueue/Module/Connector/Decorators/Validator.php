<?php
namespace DeepQueue\Module\Connector\Decorators;


use DeepQueue\Scope;
use DeepQueue\Payload;
use DeepQueue\Base\IQueueObject;
use DeepQueue\Base\IQueueConfig;
use DeepQueue\Base\Validator\IKeyValidator;
use DeepQueue\Base\Validator\IDelayValidator;
use DeepQueue\Base\Connector\Decorator\IRemoteQueueDecorator;


class Validator implements IRemoteQueueDecorator
{
	use \DeepQueue\Module\Connector\Decorators\Base\TRemoteQueueDecorator;
	
	
	private function validate(array $payload, IQueueObject $queue): void
	{
		/** @var IKeyValidator $keyValidator */
		$keyValidator = Scope::skeleton(IKeyValidator::class);
		$keyValidator->validate($payload, $queue);
		
		/** @var IDelayValidator $delayValidator */
		$delayValidator = Scope::skeleton(IDelayValidator::class);
		$delayValidator->validate($payload, $queue);
	}
	
	
	/**
	 * @param Payload[] $payload
	 * @return ?string[] IDs for each payload
	 */
	public function enqueue(array $payload): array
	{
		$queue = $this->requireQueue();
		
		$this->validate($payload, $queue);
		
		return $this->getRemoteQueue()->enqueue($payload);
	}
	
	public function dequeueWorkload(int $count = 1, IQueueConfig $config, ?float $waitSeconds = null): array
	{
		$queue = $this->requireQueue();
		
		if ($count > $queue->Config->MaxBulkSize)
		{
			$count = $queue->Config->MaxBulkSize;
		}
		
		if ($count < 0)
		{
			$count = 0;
		}
		
		return $this->getRemoteQueue()->dequeueWorkload($count, $config, $waitSeconds);
	}
}