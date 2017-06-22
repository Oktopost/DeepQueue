<?php
namespace DeepQueue\Plugins\InMemoryManager\Connector;


use DeepQueue\Base\IQueueObject;
use DeepQueue\Enums\QueueState;
use DeepQueue\Plugins\InMemoryManager\Base\IInMemoryManagerConnector;


/**
 * @autoload
 */
class InMemoryManagerConnector implements IInMemoryManagerConnector
{
	/**
	 * @autoload
	 * @var \DeepQueue\Plugins\InMemoryManager\Base\IInMemoryManagerStorage
	 */
	private $storage;
	
	
	public function upsert(IQueueObject $queue): IQueueObject
	{
		return $this->storage->pushQueue($queue);
	}

	public function load(string $queueName): ?IQueueObject
	{
		$queue = $this->storage->pullQueue($queueName);
		
		if (!$queue || $queue->State == QueueState::DELETED)
		{
			return null;
		}
		
		return $queue;
	}
}