<?php
namespace DeepQueue\Module\Manager;


use DeepQueue\Scope;
use DeepQueue\Base\IQueueConfig;
use DeepQueue\Base\IQueueObject;
use DeepQueue\Base\IDeepQueueConfig;
use DeepQueue\Base\Plugins\IManagerPlugin;
use DeepQueue\Base\Plugins\ManagerElements\IManagerDAO;
use DeepQueue\Base\Validator\IQueueObjectValidator;
use DeepQueue\Module\Ids\TimeBasedRandomGenerator;
use DeepQueue\Manager\QueueObject;


abstract class AbstractManager implements IManagerPlugin
{
	/** @var IDeepQueueConfig|null */
	private $deepConfig = null;
	
	
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
	
	
	protected abstract function getDAO(): IManagerDAO;
	
	protected abstract function getDefaultConfig(): IQueueConfig;


	public function setDeepConfig(IDeepQueueConfig $config): void
	{
		$this->deepConfig = $config;
	}

	public function load(string $name, bool $canCreate = false): ?IQueueObject
	{
		$queueObject = $this->getDAO()->loadByName($name);

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

	public function loadById(string $id): ?IQueueObject
	{
		$queueObject = $this->getDAO()->load($id);
		return $queueObject ? $this->prepare($queueObject) : null;
	}

	public function loadAll(): array
	{
		$queueObjects = $this->getDAO()->loadAll();
		
		if (!$queueObjects)
			return [];
		
		$preparedObjects = [];
		
		foreach ($queueObjects as $queueObject)
		{
			$preparedObjects[] = $this->prepare($queueObject);
		}
		
		return $preparedObjects;
	}

	public function create(IQueueObject $object): IQueueObject
	{
		$this->validate($object);
		
		$this->getDAO()->upsert($object);
		
		return $this->prepare($object);
	}

	public function update(IQueueObject $object): IQueueObject
	{
		$this->validate($object);
		
		if ($this->getDAO()->loadByName($object->Name))
		{
			$this->getDAO()->upsert($object);
		}
		
		return $this->prepare($object);
	}

	public abstract function delete($object): void;
}