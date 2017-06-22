<?php
namespace DeepQueue\Plugins\InMemoryManager;


use DeepQueue\Scope;
use DeepQueue\Enums\QueueState;
use DeepQueue\Base\IQueueObject;
use DeepQueue\Base\IDeepQueueConfig;
use DeepQueue\Module\Ids\TimeBasedRandomGenerator;
use DeepQueue\Plugins\InMemoryManager\Base\IInMemoryManager;
use DeepQueue\Plugins\InMemoryManager\Base\IInMemoryManagerConnector;
use DeepQueue\Plugins\InMemoryManager\Queue\InMemoryQueue;
use DeepQueue\Plugins\InMemoryManager\Queue\InMemoryQueueConfig;


class InMemoryManager implements IInMemoryManager
{
	/** @var IInMemoryManagerConnector */
	private $connector;
	
	/** @var IDeepQueueConfig */
	private $config = null;
	
	
	public function __construct()
	{
		$this->connector = Scope::skeleton(IInMemoryManagerConnector::class);
	}

	
	public function setConfig(IDeepQueueConfig $config): void
	{
		$this->config = $config;
	}

	public function create(IQueueObject $queueObject): IQueueObject
	{
		$queueObject = $this->connector->upsert($queueObject);
		
		$queueObject->setDeepConfig($this->config);
		
		return $queueObject;
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
		
		if ($queueObject)
		{
			$queueObject->setDeepConfig($this->config);
		}

		return $queueObject;
	}

	public function update(IQueueObject $object): IQueueObject
	{
		$queueObject = $object;
		
		if ($this->connector->load($object->Name))
		{
			$queueObject = $this->connector->upsert($object);
		}
		
		$queueObject->setDeepConfig($this->config);
		
		return $queueObject;
	}

	public function delete($object): void
	{
		if ($object instanceof IQueueObject)
		{
			$object = $object->Name;
		}
		
		$queueObject = $this->connector->load($object->Name);
		
		if (!$queueObject)
			return;
		
		$queueObject->State = QueueState::DELETED;
		
		$this->connector->upsert($queueObject);
	}
}