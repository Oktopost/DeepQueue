<?php
namespace DeepQueue\Module\Connector\Decorators;


use DeepQueue\Payload;
use DeepQueue\Base\Connector\Decorator\IRemoteQueueDecorator;


class Validator implements IRemoteQueueDecorator
{
	use \DeepQueue\Module\Connector\Decorators\Base\TEnqueueDecorator;
	use \DeepQueue\Module\Connector\Decorators\Base\TDequeueDecorator;
	
	/**
	 * @param Payload[] $payload
	 * @return ?string[] IDs for each payload
	 */
	public function enqueue(array $payload): array
	{
		$queue = $this->requireQueue();
		
		// TODO : validate
		
		return $this->getRemoteQueue()->enqueue($payload);
	}
}