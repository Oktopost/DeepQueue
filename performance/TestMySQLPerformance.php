<?php

use DeepQueue\DeepQueue;
use DeepQueue\Enums\QueueLoaderPolicy;
use DeepQueue\Plugins\Connectors\MySQLConnector\MySQLConnector;
use DeepQueue\Plugins\Managers\MySQLManager\MySQLManager;

use Serialization\Json\Serializers\PrimitiveSerializer;
use Serialization\Serializers\JsonSerializer;


require __DIR__ . '/../vendor/autoload.php';

require_once 'PerformanceTester.php';

$config = [
		'db'		=> 'deepqueue_test',
		'user'		=> 'root',
		'password'	=> '',
		'host'		=> 'localhost'
	];

$tester = new PerformanceTester();

$dq = new DeepQueue();

$dq->config()
	->setQueueNotExistsPolicy(QueueLoaderPolicy::CREATE_NEW)
	->setConnectorPlugin(new MySQLConnector($config))
	->setManagerPlugin(new MySQLManager($config))
	->setSerializer((new JsonSerializer())->add(new PrimitiveSerializer()));

$result = $tester->test($dq, 255);

var_dump($result);
