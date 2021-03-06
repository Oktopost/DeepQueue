<?php

use DeepQueue\DeepQueue;
use DeepQueue\Enums\QueueLoaderPolicy;
use DeepQueue\Plugins\Connectors\RedisConnector\RedisConnector;
use DeepQueue\Plugins\Managers\RedisManager\RedisManager;

use Serialization\Serializers\JsonSerializer;
use Serialization\Json\Serializers\PrimitiveSerializer;


require __DIR__ . '/../vendor/autoload.php';

require_once 'PerformanceTester.php';


$tester = new PerformanceTester();

$dq = new DeepQueue();

$dq->config()
	->setQueueNotExistsPolicy(QueueLoaderPolicy::CREATE_NEW)
	->setConnectorPlugin(new RedisConnector(['prefix' => 'dq.performance.test']))
	->setManagerPlugin(new RedisManager(['prefix' => 'dq.performance.test']))
	->setSerializer((new JsonSerializer())->add(new PrimitiveSerializer()));

$result = $tester->test($dq, 255);

var_dump($result);
