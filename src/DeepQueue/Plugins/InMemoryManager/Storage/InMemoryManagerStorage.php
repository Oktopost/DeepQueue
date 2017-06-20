<?php
namespace DeepQueue\Plugins\InMemoryManager\Storage;


use DeepQueue\Base\IQueueObject;
use DeepQueue\Plugins\InMemoryManager\Queue\InMemoryQueue;
use DeepQueue\Plugins\InMemoryManager\Base\IInMemoryManagerStorage;


/**
 * @autoload
 */
class InMemoryManagerStorage implements IInMemoryManagerStorage
{
	/** @var InMemoryQueue[]|array  */
	private $_queues = [];
	
	
	public function pushQueue(IQueueObject $queue): IQueueObject
	{
		$this->_queues[$queue->Name] = $queue;
		
		return $this->_queues[$queue->Name];
	}

	public function pullQueue(string $name): ?IQueueObject
	{
		return isset($this->_queues[$name]) ? $this->_queues[$name] : null;
	}
}