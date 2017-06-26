<?php
namespace DeepQueue;
/** @var $this \Skeleton\Base\IBoneConstructor */


use DeepQueue\Module\Validator\KeyValidator;
use DeepQueue\Module\Validator\DelayValidator;
use DeepQueue\Module\Validator\QueueObjectValidator;

use DeepQueue\Module\Loader\QueueLoaderBuilder;
use DeepQueue\Module\Connector\ConnectorBuilder;

use DeepQueue\Plugins\InMemoryManager\Storage\InMemoryManagerStorage;
use DeepQueue\Plugins\InMemoryManager\DAO\InMemoryManagerDAO;
use DeepQueue\Plugins\InMemoryConnector\Storage\InMemoryQueueStorage;
use DeepQueue\Plugins\InMemoryConnector\DAO\InMemoryQueueDAO;

use DeepQueue\Module\Ids\TimeBasedRandomGenerator;

use DeepQueue\Config\ConnectorProviderConfig;
use DeepQueue\Config\QueueLoaderConfig;

use DeepQueue\Plugins\RedisConnector\DAO\RedisQueueDAO;
use DeepQueue\Plugins\RedisManager\DAO\RedisManagerDAO;


$this->set(Base\Validator\IKeyValidator::class, KeyValidator::class);
$this->set(Base\Validator\IDelayValidator::class, DelayValidator::class);
$this->set(Base\Validator\IQueueObjectValidator::class, QueueObjectValidator::class);

$this->set(Base\Connector\IConnectorBuilder::class, ConnectorBuilder::class);
$this->set(Base\Loader\IQueueLoaderBuilder::class, QueueLoaderBuilder::class);

$this->set(Base\Ids\IIdGenerator::class, TimeBasedRandomGenerator::class);

$this->set(Base\Config\IConnectorProviderConfig::class, ConnectorProviderConfig::class);
$this->set(Base\Config\IQueueLoaderConfig::class, QueueLoaderConfig::class);


$this->set(Plugins\InMemoryManager\Base\IInMemoryManagerStorage::class, InMemoryManagerStorage::class);
$this->set(Plugins\InMemoryConnector\Base\IInMemoryQueueStorage::class, InMemoryQueueStorage::class);

$this->set(Plugins\InMemoryManager\Base\IInMemoryManagerDAO::class, InMemoryManagerDAO::class);
$this->set(Plugins\InMemoryConnector\Base\IInMemoryQueueDAO::class, InMemoryQueueDAO::class);

$this->set(Plugins\RedisManager\Base\IRedisManagerDAO::class, RedisManagerDAO::class);
$this->set(Plugins\RedisConnector\Base\IRedisQueueDAO::class, RedisQueueDAO::class);