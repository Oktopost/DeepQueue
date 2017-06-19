<?php
namespace DeepQueue\Module\Connector\Decorators\Base;


use DeepQueue\Workload;


trait TDequeueDecorator
{
	use TRemoteQueueDecorator;
	
	
	/**
	 * @return Workload[]
	 */
	public function dequeueWorkload(int $count = 1, ?float $waitSeconds = null): array
	{
		return $this->getRemoteQueue()->dequeueWorkload($count, $waitSeconds);
	}
	
}