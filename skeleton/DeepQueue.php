<?php
namespace DeepQueue;
/** @var $this \Skeleton\Base\IBoneConstructor */


use DeepQueue\Module\Validator\KeyValidator;
use DeepQueue\Module\Validator\DelayValidator;
use DeepQueue\Module\Validator\QueueObjectValidator;

use DeepQueue\Module\Loader\QueueLoaderBuilder;
use DeepQueue\Module\Connector\ConnectorBuilder;

use DeepQueue\Plugins\Managers\InMemoryManager\Storage\InMemoryManagerStorage;
use DeepQueue\Plugins\Managers\InMemoryManager\DAO\InMemoryManagerDAO;
use DeepQueue\Plugins\Connectors\InMemoryConnector\Storage\InMemoryQueueStorage;
use DeepQueue\Plugins\Connectors\InMemoryConnector\DAO\InMemoryQueueDAO;

use DeepQueue\Utils\TimeBasedRandomIdGenerator;

use DeepQueue\Config\ConnectorProviderConfig;
use DeepQueue\Config\QueueLoaderConfig;

use DeepQueue\Plugins\Connectors\MySQLConnector\DAO\MySQLQueueDAO;
use DeepQueue\Plugins\Managers\MySQLManager\DAO\Connector\MySQLManagerConnector;
use DeepQueue\Plugins\Connectors\RedisConnector\DAO\RedisQueueDAO;
use DeepQueue\Plugins\Managers\RedisManager\DAO\RedisManagerDAO;

use DeepQueue\Plugins\Managers\MySQLManager\DAO\MySQLManagerDAO;


$this->set(Base\Validator\IKeyValidator::class, KeyValidator::class);
$this->set(Base\Validator\IDelayValidator::class, DelayValidator::class);
$this->set(Base\Validator\IQueueObjectValidator::class, QueueObjectValidator::class);

$this->set(Base\Connector\IConnectorBuilder::class, ConnectorBuilder::class);
$this->set(Base\Loader\IQueueLoaderBuilder::class, QueueLoaderBuilder::class);

$this->set(Base\Utils\IIdGenerator::class, TimeBasedRandomIdGenerator::class);

$this->set(Base\Config\IConnectorProviderConfig::class, ConnectorProviderConfig::class);
$this->set(Base\Config\IQueueLoaderConfig::class, QueueLoaderConfig::class);


$this->set(Plugins\Managers\InMemoryManager\Base\IInMemoryManagerStorage::class, InMemoryManagerStorage::class);
$this->set(Plugins\Connectors\InMemoryConnector\Base\IInMemoryQueueStorage::class, InMemoryQueueStorage::class);

$this->set(Plugins\Managers\InMemoryManager\Base\IInMemoryManagerDAO::class, InMemoryManagerDAO::class);
$this->set(Plugins\Connectors\InMemoryConnector\Base\IInMemoryQueueDAO::class, InMemoryQueueDAO::class);

$this->set(Plugins\Managers\RedisManager\Base\IRedisManagerDAO::class, RedisManagerDAO::class);
$this->set(Plugins\Connectors\RedisConnector\Base\IRedisQueueDAO::class, RedisQueueDAO::class);

$this->set(Plugins\Managers\MySQLManager\Base\DAO\IMySQLManagerDAO::class, MySQLManagerDAO::class);
$this->set(Plugins\Managers\MySQLManager\Base\DAO\Connector\IMySQLManagerConnector::class, MySQLManagerConnector::class);

$this->set(Plugins\Connectors\MySQLConnector\Base\DAO\IMySQLQueueDAO::class, MySQLQueueDAO::class);