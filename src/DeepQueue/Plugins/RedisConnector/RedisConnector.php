<?php
namespace DeepQueue\Plugins\RedisConnector;


use DeepQueue\Scope;
use DeepQueue\Base\IMetaData;
use DeepQueue\Base\IQueueObject;
use DeepQueue\Base\IDeepQueueConfig;
use DeepQueue\Base\Config\IRedisConfig;
use DeepQueue\Base\Queue\Remote\IRemoteQueue;
use DeepQueue\Utils\RedisConfigParser;
use DeepQueue\Plugins\RedisConnector\Base\IRedisQueueDAO;
use DeepQueue\Plugins\RedisConnector\Base\IRedisConnector;
use DeepQueue\Plugins\RedisConnector\Queue\RedisQueue;
use DeepQueue\Plugins\RedisConnector\Manager\RedisQueueManager;


class RedisConnector implements IRedisConnector
{
	/** @var IDeepQueueConfig|null */
	private $deepConfig = null;

	/** @var IRedisQueueDAO */
	private $dao;
	
	
	/**
	 * @param IRedisConfig|array $redisConfig
	 */
	public function __construct($redisConfig)
	{
		$redisConfig = RedisConfigParser::parse($redisConfig);
		
		$this->dao = Scope::skeleton(IRedisQueueDAO::class);
		$this->dao->initClient($redisConfig);
	}
	
	
	public function setDeepConfig(IDeepQueueConfig $config): void
	{
		$this->deepConfig = $config;
	}

	public function getMetaData(IQueueObject $queueObject): IMetaData
	{
		$manager = new RedisQueueManager($queueObject, $this->dao);
		
		return $manager->getMetadata();
	}

	public function getQueue(string $name): IRemoteQueue
	{
		return new RedisQueue($name, $this->dao, $this->deepConfig->serializer(), $this->deepConfig->logger());
	}
}