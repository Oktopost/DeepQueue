<?php
namespace DeepQueue\Plugins\Managers\InMemoryManager\Storage;


use DeepQueue\Base\IQueueObject;
use DeepQueue\Plugins\Managers\InMemoryManager\Base\IInMemoryManagerStorage;


/**
 * @unique
 * @autoload
 */
class InMemoryManagerStorage implements IInMemoryManagerStorage
{
	/** @var IQueueObject[]|array  */
	private $queues = [];
	
	/** @var string[]|array */
	private $queueNames = [];
	
	
	public function pushQueue(IQueueObject $queue): IQueueObject
	{
		$this->queues[$queue->Name] = $queue;
		$this->queueNames[$queue->Id] = $queue->Name; 
		
		return $this->queues[$queue->Name];
	}

	public function pullQueue(string $name): ?IQueueObject
	{
		return isset($this->queues[$name]) ? $this->queues[$name] : null;
	}
	
	public function pullQueueById(string $id): ?IQueueObject
	{
		$queue = null;
		
		if (isset($this->queueNames[$id]) && isset($this->queues[$this->queueNames[$id]]))
		{
			$queue = $this->queues[$this->queueNames[$id]];
		}
		
		return $queue;
	}
	
	public function getAll(): array
	{
		return $this->queues;
	}
}