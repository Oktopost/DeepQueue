<?php
namespace DeepQueue\Plugins\InMemoryManager;


use DeepQueue\Scope;
use DeepQueue\Enums\Policy;
use DeepQueue\Enums\QueueState;
use DeepQueue\Base\IQueueConfig;
use DeepQueue\Base\IQueueObject;
use DeepQueue\Base\IDeepQueueConfig;
use DeepQueue\Manager\QueueConfig;
use DeepQueue\Manager\QueueObject;
use DeepQueue\Module\Ids\TimeBasedRandomGenerator;
use DeepQueue\Plugins\InMemoryManager\Base\IInMemoryManager;
use DeepQueue\Plugins\InMemoryManager\Base\IInMemoryManagerStorage;
use DeepQueue\Plugins\InMemoryManager\Base\IInMemoryManagerConnector;


class InMemoryManager implements IInMemoryManager
{
	/** @var IInMemoryManagerStorage */
	private $storage;
	
	/** @var IInMemoryManagerConnector */
	private $connector;
	
	/** @var IDeepQueueConfig */
	private $deepConfig = null;
	
	/** @var IQueueConfig */
	private $defaultQueueConfig = null;
	
	
	private function getDefaultConfig(): IQueueConfig
	{
		if (!$this->defaultQueueConfig)
		{
			$this->defaultQueueConfig = new QueueConfig();
			$this->defaultQueueConfig->UniqueKeyPolicy = Policy::ALLOWED;
			$this->defaultQueueConfig->DelayPolicy = Policy::IGNORED;
			$this->defaultQueueConfig->MaxBulkSize = 256;
		}
		
		return clone $this->defaultQueueConfig;
	}
	
	
	public function __construct()
	{
		$this->storage = Scope::skeleton(IInMemoryManagerStorage::class);
		$this->connector = Scope::skeleton(IInMemoryManagerConnector::class);
	}

	
	public function setDeepConfig(IDeepQueueConfig $deepConfig): void
	{
		$this->deepConfig = $deepConfig;
	}

	public function create(IQueueObject $queueObject): IQueueObject
	{
		$queueObject = $this->connector->upsert($queueObject);
		
		$queueObject->setDeepConfig($this->deepConfig);
		
		return $queueObject;
	}

	public function load(string $name, bool $canCreate = false): ?IQueueObject
	{
		$queueObject = $this->connector->load($name);
		
		if (!$queueObject && $canCreate)
		{
			$queueObject = new QueueObject();
			$queueObject->Name = $name;
			$queueObject->Id = (new TimeBasedRandomGenerator())->get();
			$queueObject->Config = $this->getDefaultConfig();
			
			return $this->create($queueObject);
		}
		
		if ($queueObject)
		{
			$queueObject->setDeepConfig($this->deepConfig);
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
		
		$queueObject->setDeepConfig($this->deepConfig);
		
		return $queueObject;
	}

	public function delete($object): void
	{
		if ($object instanceof IQueueObject)
		{
			$object = $object->Id;
		}
		
		$queueObject = $this->connector->loadById($object);
		
		if (!$queueObject)
			return;
		
		$queueObject->State = QueueState::DELETED;
		
		$this->connector->upsert($queueObject);
	}
}