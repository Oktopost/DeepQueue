<?php
namespace DeepQueue\Module\Connector\Decorators;


use DeepQueue\Base\IQueueObject;
use DeepQueue\Base\Validator\IDelayValidator;
use DeepQueue\Base\Validator\IKeyValidator;
use DeepQueue\Payload;
use DeepQueue\Base\Connector\Decorator\IRemoteQueueDecorator;
use DeepQueue\Scope;


class Validator implements IRemoteQueueDecorator
{
	use \DeepQueue\Module\Connector\Decorators\Base\TDequeueDecorator;
	
	
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
}