<?php
namespace DeepQueue\Plugins\Managers\RedisManager;


use DeepQueue\Scope;
use DeepQueue\Enums\Policy;
use DeepQueue\Base\IQueueConfig;
use DeepQueue\Base\IQueueObject;
use DeepQueue\Base\Config\IRedisConfig;
use DeepQueue\Base\Plugins\ManagerElements\IManagerDAO;
use DeepQueue\Manager\QueueConfig;
use DeepQueue\Utils\RedisConfigParser;
use DeepQueue\Plugins\Managers\AbstractManager;
use DeepQueue\Plugins\Managers\RedisManager\Base\IRedisManager;
use DeepQueue\Plugins\Managers\RedisManager\Base\IRedisManagerDAO;


class RedisManager extends AbstractManager implements IRedisManager 
{
	/** @var IQueueConfig|null */
	private $defaultQueueConfig = null;
	
	/** @var IRedisManagerDAO */
	private $dao;

	
	protected function getDefaultConfig(): IQueueConfig
	{
		if (!$this->defaultQueueConfig)
		{
			$this->defaultQueueConfig = new QueueConfig();
			$this->defaultQueueConfig->UniqueKeyPolicy = Policy::ALLOWED;
			$this->defaultQueueConfig->DelayPolicy = Policy::ALLOWED;
			$this->defaultQueueConfig->MaxBulkSize = 256;
			$this->defaultQueueConfig->MaximalDelay = -1;
			$this->defaultQueueConfig->MinimalDelay = -1;
			$this->defaultQueueConfig->DefaultDelay = 0;
		}

		return clone $this->defaultQueueConfig;
	}
	
	protected function getDAO(): IManagerDAO
	{
		return $this->dao;
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

	public function delete($object): void
	{
		if ($object instanceof IQueueObject)
		{
			$object = $object->Id;
		}
		
		$this->dao->delete($object);
	}
}