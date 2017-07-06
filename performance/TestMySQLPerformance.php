<?php

use DeepQueue\DeepQueue;
use DeepQueue\Enums\QueueLoaderPolicy;
use DeepQueue\Plugins\MySQLConnector\MySQLConnector;
use DeepQueue\Plugins\MySQLManager\MySQLManager;

use Serialization\Json\Serializers\PrimitiveSerializer;
use Serialization\Serializers\JsonSerializer;


require '../vendor/autoload.php';

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
