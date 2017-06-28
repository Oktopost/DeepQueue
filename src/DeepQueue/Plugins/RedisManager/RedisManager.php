<?php
namespace DeepQueue\Plugins\RedisManager;


use DeepQueue\Scope;
use DeepQueue\Enums\Policy;
use DeepQueue\Base\IQueueConfig;
use DeepQueue\Base\IQueueObject;
use DeepQueue\Base\IDeepQueueConfig;
use DeepQueue\Base\Config\IRedisConfig;
use DeepQueue\Base\Validator\IQueueObjectValidator;
use DeepQueue\Utils\RedisConfigParser;
use DeepQueue\Module\Ids\TimeBasedRandomGenerator;
use DeepQueue\Manager\QueueConfig;
use DeepQueue\Manager\QueueObject;
use DeepQueue\Plugins\RedisManager\Base\IRedisManager;
use DeepQueue\Plugins\RedisManager\Base\IRedisManagerDAO;


class RedisManager implements IRedisManager
{
	/** @var IDeepQueueConfig|null */
	private $deepConfig = null;
	
	/** @var IQueueConfig|null */
	private $defaultQueueConfig = null;
	
	/** @var IRedisManagerDAO */
	private $dao;
	
	
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
	

	/**
	 * @param IRedisConfig|array $redisConfig
	 */
	public function __construct($redisConfig)
	{
		$redisConfig = RedisConfigParser::parse($redisConfig);
		
		$this->dao = Scope::skeleton(IRedisManagerDAO::class);
		$this->dao->initClient($redisConfig);
	}

	
	public function setTTL(int $seconds): void
	{
		$this->dao->setTTL($seconds);
	}

	public function setDeepConfig(IDeepQueueConfig $config): void
	{
		$this->deepConfig = $config;
	}

	public function create(IQueueObject $object): IQueueObject
	{
		$this->validate($object);
		
		$queueObject = $this->dao->upsert($object);
		
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
		
		$this->dao->delete($object);
	}
}