<?php
namespace DeepQueue\Plugins\Connectors\MySQLConnector;


use DeepQueue\Base\Plugins\ConnectorElements\IQueueManager;
use DeepQueue\Scope;
use DeepQueue\Base\IMetaData;
use DeepQueue\Base\IQueueObject;
use DeepQueue\Base\IDeepQueueConfig;
use DeepQueue\Base\Queue\Remote\IRemoteQueue;
use DeepQueue\Plugins\Connectors\MySQLConnector\Base\DAO\IMySQLQueueDAO;
use DeepQueue\Plugins\Connectors\MySQLConnector\Base\IMySQLConnector;
use DeepQueue\Plugins\Connectors\MySQLConnector\Queue\MySQLQueue;
use DeepQueue\Plugins\Connectors\MySQLConnector\Manager\MySQLQueueManager;


class MySQLConnector implements IMySQLConnector
{
	/** @var IDeepQueueConfig|null */
	private $deepConfig = null;

	/** @var MySQLQueueManager */
	private $manager;
	
	/** @var IMySQLQueueDAO */
	private $dao;


	/**
	 * @param array|\Squid\MySql\IMySqlConnector $config
	 */
	public function __construct($config)
	{
		$this->dao = Scope::skeleton(IMySQLQueueDAO::class);
		$this->dao->initConnector($config);
		
		$this->manager = new MySQLQueueManager($this->dao);
	}
	
	
	public function setDeepConfig(IDeepQueueConfig $config): void
	{
		$this->deepConfig = $config;
	}

	public function manager(string $queueName): IQueueManager
	{
		$this->manager->setQueueName($queueName);
		return $this->manager;
	}
	
	public function getQueue(string $name): IRemoteQueue
	{
		return new MySQLQueue($name, $this->dao, $this->deepConfig->serializer(), $this->deepConfig->logger());
	}
}