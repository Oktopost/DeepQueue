<?php
namespace DeepQueue\Plugins\Managers\InMemoryManager;


use DeepQueue\Scope;
use DeepQueue\Enums\Policy;
use DeepQueue\Enums\QueueState;
use DeepQueue\Base\IQueueConfig;
use DeepQueue\Base\IQueueObject;
use DeepQueue\Base\Plugins\ManagerElements\IManagerDAO;
use DeepQueue\Manager\QueueConfig;
use DeepQueue\Module\Manager\AbstractManager;
use DeepQueue\Plugins\Managers\InMemoryManager\Base\IInMemoryManager;
use DeepQueue\Plugins\Managers\InMemoryManager\Base\IInMemoryManagerStorage;
use DeepQueue\Plugins\Managers\InMemoryManager\Base\IInMemoryManagerDAO;


class InMemoryManager extends AbstractManager implements IInMemoryManager
{
	/** @var IInMemoryManagerStorage */
	private $storage;
	
	/** @var IInMemoryManagerDAO */
	private $dao;
	
	/** @var IQueueConfig|null */
	private $defaultQueueConfig = null;


	protected function getDefaultConfig(): IQueueConfig
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
	
	protected function getDAO(): IManagerDAO
	{
		return $this->dao;
	}


	public function __construct()
	{
		$this->storage = Scope::skeleton(IInMemoryManagerStorage::class);
		$this->dao = Scope::skeleton(IInMemoryManagerDAO::class);
	}


	public function delete($object): void
	{
		if ($object instanceof IQueueObject)
		{
			$object = $object->Id;
		}
		
		$queueObject = $this->dao->load($object);
		
		if (!$queueObject)
			return;
		
		$queueObject->State = QueueState::DELETED;
		
		$this->dao->upsert($queueObject);
	}
}