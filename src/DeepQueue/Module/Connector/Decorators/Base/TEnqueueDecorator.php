<?php
namespace DeepQueue\Module\Connector\Decorators\Base;


use DeepQueue\Payload;


trait TEnqueueDecorator
{
	use TRemoteQueueDecorator;
	
	
	/**
	 * @param Payload[] $payload
	 * @return string[] IDs for each payload
	 */
	public function enqueue(array $payload): array
	{
		return $this->getRemoteQueue()->enqueue($payload);
	}
}