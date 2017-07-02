<?php
namespace DeepQueue\Plugins\MySQLManager;


use DeepQueue\Scope;
use DeepQueue\Base\IQueueConfig;
use DeepQueue\Base\IQueueObject;
use DeepQueue\Base\IDeepQueueConfig;
use DeepQueue\Base\Validator\IQueueObjectValidator;
use DeepQueue\Enums\Policy;
use DeepQueue\Enums\QueueState;
use DeepQueue\Manager\QueueConfig;
use DeepQueue\Manager\QueueObject;
use DeepQueue\Module\Ids\TimeBasedRandomGenerator;
use DeepQueue\Plugins\MySQLManager\Base\IMySQLManager;
use DeepQueue\Plugins\MySQLManager\Base\IMySQLManagerDAO;
use Squid\MySql;


class MySQLManager implements IMySQLManager
{
	/** @var IDeepQueueConfig|null */
	private $deepConfig = null;
	
	/** @var IQueueConfig|null */
	private $defaultQueueConfig = null;
	
	/** @var IMySQLManagerDAO */
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
	
	
	
	public function __construct(array $config)
	{
		$sql = new MySql();
		$sql->config()->setConfig($config);
		
		$connector = $sql->getConnector();
		
		$this->dao = Scope::skeleton(IMySQLManagerDAO::class);
		$this->dao->setConnector($connector);
	}


	public function setDeepConfig(IDeepQueueConfig $config): void
	{
		$this->deepConfig = $config;
	}

	public function load(string $name, bool $canCreate = false): ?IQueueObject
	{
		$queueObject = $this->dao->loadByName($name);

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

	public function create(IQueueObject $object): IQueueObject
	{
		$this->validate($object);
		
		$queueObject = $this->dao->upsert($object);
		
		return $this->prepare($queueObject);
	}

	public function update(IQueueObject $object): IQueueObject
	{
		$this->validate($object);
		
		if ($this->dao->loadByName($object->Name))
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