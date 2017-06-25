<?php
namespace DeepQueue\Plugins\RedisManager;


use DeepQueue\Scope;
use DeepQueue\Enums\Policy;
use DeepQueue\Base\IQueueConfig;
use DeepQueue\Base\IQueueObject;
use DeepQueue\Base\IDeepQueueConfig;
use DeepQueue\Module\Ids\TimeBasedRandomGenerator;
use DeepQueue\Manager\QueueConfig;
use DeepQueue\Manager\QueueObject;
use DeepQueue\Plugins\RedisManager\Base\IRedisManager;
use DeepQueue\Plugins\RedisManager\Base\IRedisManagerConnector;


class RedisManager implements IRedisManager
{
	/** @var IDeepQueueConfig|null */
	private $deepConfig = null;
	
	/** @var IQueueConfig|null */
	private $defaultQueueConfig = null;
	
	/** @var IRedisManagerConnector */
	private $connector;
	
	
	private function getDefaultConfig(): IQueueConfig
	{
		if (!$this->defaultQueueConfig)
		{
			$this->defaultQueueConfig = new QueueConfig();
			$this->defaultQueueConfig->UniqueKeyPolicy = Policy::ALLOWED;
			$this->defaultQueueConfig->DelayPolicy = Policy::ALLOWED;
			$this->defaultQueueConfig->MaxBulkSize = 256;
			$this->defaultQueueConfig->MaximalDelay = 5;
			$this->defaultQueueConfig->DefaultDelay = 1;
		}
		
		return clone $this->defaultQueueConfig;
	}
	
	private function prepare(IQueueObject $queueObject): IQueueObject
	{
		$queueObject->setDeepConfig($this->deepConfig);
		return $queueObject;
	}
	
	
	public function __construct()
	{
		$this->connector = Scope::skeleton(IRedisManagerConnector::class);
	}


	public function setDeepConfig(IDeepQueueConfig $config): void
	{
		$this->deepConfig = $config;
	}

	public function create(IQueueObject $object): IQueueObject
	{
		$queueObject = $this->connector->upsert($object);
		
		return $this->prepare($queueObject);
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

		return $queueObject ? $this->prepare($queueObject) : null;
	}

	public function update(IQueueObject $object): IQueueObject
	{
		$queueObject = $object;
		
		if ($this->connector->load($object->Name))
		{
			$queueObject = $this->connector->upsert($object);
		}
		
		return $this->prepare($queueObject);
	}

	public function delete($object): void
	{
		if ($object instanceof IQueueObject)
		{
			$object = $object->Id;
		}
		
		$this->connector->delete($object);
	}
}