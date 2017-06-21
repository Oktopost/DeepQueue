<?php
namespace DeepQueue;
/** @var $this \Skeleton\Base\IBoneConstructor */

use Skeleton\Type;

use DeepQueue\Module\Validator\KeyValidator;
use DeepQueue\Module\Validator\DelayValidator;

use DeepQueue\Module\Loader\QueueLoaderBuilder;
use DeepQueue\Module\Connector\ConnectorBuilder;

use DeepQueue\Plugins\InMemoryManager\Storage\InMemoryManagerStorage;
use DeepQueue\Plugins\InMemoryManager\Connector\InMemoryManagerConnector;
use DeepQueue\Plugins\InMemoryRemote\Storage\InMemoryRemoteStorage;
use DeepQueue\Plugins\InMemoryRemote\Connector\InMemoryQueueConnector;

use DeepQueue\Module\Ids\TimeBasedRandomGenerator;


$this->set(Base\Validator\IKeyValidator::class, KeyValidator::class);
$this->set(Base\Validator\IDelayValidator::class, DelayValidator::class);

$this->set(Base\Connector\IConnectorBuilder::class, ConnectorBuilder::class);
$this->set(Base\Loader\IQueueLoaderBuilder::class, QueueLoaderBuilder::class);

$this->set(Plugins\InMemoryManager\Base\IInMemoryManagerStorage::class, InMemoryManagerStorage::class, Type::Singleton);
$this->set(Plugins\InMemoryManager\Base\IInMemoryManagerConnector::class, InMemoryManagerConnector::class);

$this->set(Plugins\InMemoryRemote\Base\IInMemoryRemoteStorage::class, InMemoryRemoteStorage::class, Type::Singleton);
$this->set(Plugins\InMemoryRemote\Base\IInMemoryQueueConnector::class, InMemoryQueueConnector::class);

$this->set(Base\Ids\IIdGenerator::class, TimeBasedRandomGenerator::class);
