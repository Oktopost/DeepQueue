<?php
namespace DeepQueue;
/** @var $this \Skeleton\Base\IBoneConstructor */

use DeepQueue\Plugins\InMemoryRemote\Storage\InMemoryRemoteStorage;
use Skeleton\Type;

use DeepQueue\Module\Loader\QueueLoaderBuilder;
use DeepQueue\Module\Connector\ConnectorBuilder;

use DeepQueue\Plugins\InMemoryManager\Storage\InMemoryManagerStorage;
use DeepQueue\Plugins\InMemoryManager\Connector\InMemoryManagerConnector;
use DeepQueue\Plugins\InMemoryRemote\Connector\InMemoryQueueConnector;


$this->set(Base\Connector\IConnectorBuilder::class, ConnectorBuilder::class);
$this->set(Base\Loader\IQueueLoaderBuilder::class, QueueLoaderBuilder::class);

$this->set(Plugins\InMemoryManager\Base\IInMemoryManagerStorage::class, InMemoryManagerStorage::class, Type::Singleton);
$this->set(Plugins\InMemoryManager\Base\IInMemoryManagerConnector::class, InMemoryManagerConnector::class);

$this->set(Plugins\InMemoryRemote\Base\IInMemoryRemoteStorage::class, InMemoryRemoteStorage::class, Type::Singleton);
$this->set(Plugins\InMemoryRemote\Base\IInMemoryQueueConnector::class, InMemoryQueueConnector::class);
