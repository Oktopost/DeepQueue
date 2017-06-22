<?php
namespace DeepQueue\Plugins\InMemoryManager;


use DeepQueue\Scope;
use DeepQueue\Enums\QueueState;
use DeepQueue\Base\IQueueObject;
use DeepQueue\Module\Ids\TimeBasedRandomGenerator;
use DeepQueue\Plugins\InMemoryManager\Base\IInMemoryManager;
use DeepQueue\Plugins\InMemoryManager\Base\IInMemoryManagerConnector;
use DeepQueue\Plugins\InMemoryManager\Queue\InMemoryQueue;
use DeepQueue\Plugins\InMemoryManager\Queue\InMemoryQueueConfig;


class InMemoryManager implements IInMemoryManager
{
	/** @var IInMemoryManagerConnector */
	private $connector;
	
	
	public function __construct()
	{
		$this->connector = Scope::skeleton(IInMemoryManagerConnector::class);
	}


	public function create(IQueueObject $queue): IQueueObject
	{
		return $this->connector->upsert($queue);
	}

	public function load(string $name, bool $canCreate = false): ?IQueueObject
	{
		$queueObject = $this->connector->load($name);
		
		if (!$queueObject && $canCreate)
		{
			$config = new InMemoryQueueConfig();
			
			$queueObject = new InMemoryQueue();
			$queueObject->Name = $name;
			$queueObject->ID = (new TimeBasedRandomGenerator())->get();
			$queueObject->Config = $config;
			
			return $this->create($queueObject);
		}

		return $queueObject;
	}

	public function update(IQueueObject $object): IQueueObject
	{
		$queue = null;
		
		if ($this->connector->load($object->Name))
		{
			$queue = $this->connector->upsert($object);
		}
		
		return $queue;
	}

	public function delete($object): void
	{
		if ($object instanceof IQueueObject)
		{
			$object = $object->Name;
		}
		
		$queue = $this->connector->load($object->Name);
		
		if (!$queue)
			return;
		
		$queue->State = QueueState::DELETED;
		
		$this->connector->load($queue);
	}
}