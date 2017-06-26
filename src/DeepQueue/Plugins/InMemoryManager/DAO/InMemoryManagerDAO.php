<?php
namespace DeepQueue\Plugins\InMemoryManager\DAO;


use DeepQueue\Base\IQueueObject;
use DeepQueue\Enums\QueueState;
use DeepQueue\Plugins\InMemoryManager\Base\IInMemoryManagerDAO;


/**
 * @autoload
 */
class InMemoryManagerDAO implements IInMemoryManagerDAO
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
	
	public function loadById(string $queueId): ?IQueueObject
	{
		$queue = $this->storage->pullQueueById($queueId);
		
		if (!$queue || $queue->State == QueueState::DELETED)
		{
			return null;
		}
		
		return $queue;
	}
}