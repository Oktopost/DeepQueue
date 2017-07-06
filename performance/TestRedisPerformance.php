<?php

use DeepQueue\DeepQueue;
use DeepQueue\Enums\QueueLoaderPolicy;
use DeepQueue\Plugins\RedisConnector\RedisConnector;
use DeepQueue\Plugins\RedisManager\RedisManager;

use Serialization\Serializers\JsonSerializer;
use Serialization\Json\Serializers\PrimitiveSerializer;


require '../vendor/autoload.php';

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
