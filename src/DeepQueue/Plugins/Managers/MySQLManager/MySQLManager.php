<?php
namespace DeepQueue\Plugins\Managers\MySQLManager;


use DeepQueue\Scope;
use DeepQueue\Base\IQueueConfig;
use DeepQueue\Base\IQueueObject;
use DeepQueue\Base\Plugins\ManagerElements\IManagerDAO;
use DeepQueue\Enums\Policy;
use DeepQueue\Enums\QueueState;
use DeepQueue\Manager\QueueConfig;
use DeepQueue\Plugins\Managers\AbstractManager;
use DeepQueue\Plugins\Managers\MySQLManager\Base\IMySQLManager;
use DeepQueue\Plugins\Managers\MySQLManager\Base\DAO\IMySQLManagerDAO;

use Squid\MySql\IMySqlConnector;


class MySQLManager extends AbstractManager implements IMySQLManager
{
	/** @var IQueueConfig|null */
	private $defaultQueueConfig = null;
	
	/** @var IMySQLManagerDAO */
	private $dao;

	
	protected function getDefaultConfig(): IQueueConfig
	{
		if (!$this->defaultQueueConfig)
		{
			$this->defaultQueueConfig = new QueueConfig();
			$this->defaultQueueConfig->UniqueKeyPolicy = Policy::ALLOWED;
			$this->defaultQueueConfig->DelayPolicy = Policy::ALLOWED;
			$this->defaultQueueConfig->MaxBulkSize = 256;
			$this->defaultQueueConfig->DefaultDelay = 0;
		}

		return clone $this->defaultQueueConfig;
	}
	
	protected function getDAO(): IManagerDAO
	{
		return $this->dao;
	}

	/**
	 * @param array|IMySqlConnector $config
	 */
	public function __construct($config)
	{
		$this->dao = Scope::skeleton(IMySQLManagerDAO::class);
		$this->dao->initConnector($config);
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