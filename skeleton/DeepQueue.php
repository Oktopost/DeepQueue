<?php
namespace DeepQueue;
/** @var $this \Skeleton\Base\IBoneConstructor */

use Skeleton\Type;

use DeepQueue\Module\Loader\QueueLoaderBuilder;
use DeepQueue\Module\Connector\ConnectorBuilder;

use DeepQueue\Plugins\InMemoryManager\Storage\InMemoryStorage;
use DeepQueue\Plugins\InMemoryManager\Connector\InMemoryManagerConnector;
use DeepQueue\Plugins\InMemoryRemote\Connector\InMemoryQueueConnector;


$this->set(Base\Connector\IConnectorBuilder::class, ConnectorBuilder::class);
$this->set(Base\Loader\IQueueLoaderBuilder::class, QueueLoaderBuilder::class);

$this->set(Plugins\InMemoryManager\Base\Storage\IInMemoryStorage::class, InMemoryStorage::class, Type::Singleton);
$this->set(Plugins\InMemoryManager\Base\IInMemoryManagerConnector::class, InMemoryManagerConnector::class);
$this->set(Plugins\InMemoryRemote\Base\IInMemoryQueueConnector::class, InMemoryQueueConnector::class);
