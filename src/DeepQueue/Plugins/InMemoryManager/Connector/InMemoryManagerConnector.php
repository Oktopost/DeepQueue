<?php
namespace DeepQueue\Plugins\InMemoryManager\Connector;


use DeepQueue\Base\IQueueObject;
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
		return $this->storage->pullQueue($queueName);
	}
}