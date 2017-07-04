<?php
namespace DeepQueue\Plugins\MySQLConnector;


use DeepQueue\Scope;
use DeepQueue\Base\IMetaData;
use DeepQueue\Base\IQueueObject;
use DeepQueue\Base\IDeepQueueConfig;
use DeepQueue\Base\Queue\Remote\IRemoteQueue;
use DeepQueue\Plugins\MySQLConnector\Base\DAO\IMySQLQueueDAO;
use DeepQueue\Plugins\MySQLConnector\Base\IMySQLConnector;
use DeepQueue\Plugins\MySQLConnector\Queue\MySQLQueue;
use DeepQueue\Plugins\MySQLConnector\Manager\MySQLQueueManager;


class MySQLConnector implements IMySQLConnector
{
	/** @var IDeepQueueConfig|null */
	private $deepConfig = null;

	/** @var IMySQLQueueDAO */
	private $dao;
	
	
	public function __construct(array $config)
	{
		$this->dao = Scope::skeleton(IMySQLQueueDAO::class);
		$this->dao->initConnector($config);
	}
	
	
	public function setDeepConfig(IDeepQueueConfig $config): void
	{
		$this->deepConfig = $config;
	}

	public function getMetaData(IQueueObject $queueObject): IMetaData
	{
		$manager = new MySQLQueueManager($queueObject, $this->dao);
		
		return $manager->getMetadata();
	}

	public function getQueue(string $name): IRemoteQueue
	{
		return new MySQLQueue($name, $this->dao, $this->deepConfig->serializer(), $this->deepConfig->logger());
	}
}