<?php
namespace DeepQueue\Plugins\InMemoryManager;


use DeepQueue\Base\Validator\IQueueObjectValidator;
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
use DeepQueue\Plugins\InMemoryManager\Base\IInMemoryManagerDAO;


class InMemoryManager implements IInMemoryManager
{
	/** @var IInMemoryManagerStorage */
	private $storage;
	
	/** @var IInMemoryManagerDAO */
	private $dao;
	
	/** @var IDeepQueueConfig|null */
	private $deepConfig = null;
	
	/** @var IQueueConfig|null */
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
	
	private function prepare(IQueueObject $queueObject): IQueueObject
	{
		$queueObject->setDeepConfig($this->deepConfig);
		return $queueObject;
	}
	
	private function getId(): string
	{
		return (new TimeBasedRandomGenerator())->get();
	}
	
	private function validate(IQueueObject $queueObject): void
	{
		if (!$queueObject->Id)
		{
			$queueObject->Id = $this->getId();
		}
		
		/** @var IQueueObjectValidator $validator */
		$validator = Scope::skeleton(IQueueObjectValidator::class);
		
		$validator->validate($queueObject);
	}
	
	
	public function __construct()
	{
		$this->storage = Scope::skeleton(IInMemoryManagerStorage::class);
		$this->dao = Scope::skeleton(IInMemoryManagerDAO::class);
	}

	
	public function setDeepConfig(IDeepQueueConfig $deepConfig): void
	{
		$this->deepConfig = $deepConfig;
	}

	public function create(IQueueObject $queueObject): IQueueObject
	{
		$this->validate($queueObject);
		
		$queueObject = $this->dao->upsert($queueObject);
		
		return $this->prepare($queueObject);
	}

	public function load(string $name, bool $canCreate = false): ?IQueueObject
	{
		$queueObject = $this->dao->load($name);
		
		if (!$queueObject && $canCreate)
		{
			$queueObject = new QueueObject();
			$queueObject->Name = $name;
			$queueObject->Id = $this->getId();
			$queueObject->Config = $this->getDefaultConfig();
			
			return $this->create($queueObject);
		}

		return $queueObject ? $this->prepare($queueObject) : null;
	}

	public function update(IQueueObject $object): IQueueObject
	{
		$this->validate($object);

		if ($this->dao->load($object->Name))
		{
			$object = $this->dao->upsert($object);
		}
		
		return $this->prepare($object);
	}

	public function delete($object): void
	{
		if ($object instanceof IQueueObject)
		{
			$object = $object->Id;
		}
		
		$queueObject = $this->dao->loadById($object);
		
		if (!$queueObject)
			return;
		
		$queueObject->State = QueueState::DELETED;
		
		$this->dao->upsert($queueObject);
	}
}