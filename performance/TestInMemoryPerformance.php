<?php
use DeepQueue\DeepQueue;
use DeepQueue\Enums\QueueLoaderPolicy;
use DeepQueue\Plugins\InMemoryConnector\InMemoryConnector;
use DeepQueue\Plugins\InMemoryManager\InMemoryManager;

use Serialization\Serializers\JsonSerializer;
use Serialization\Json\Serializers\PrimitiveSerializer;


require '../vendor/autoload.php';

require_once 'PerformanceTester.php';

$tester = new PerformanceTester();

$dq = new DeepQueue();

$dq->config()
	->setQueueNotExistsPolicy(QueueLoaderPolicy::CREATE_NEW)
	->setConnectorPlugin(new InMemoryConnector())
	->setManagerPlugin(new InMemoryManager())
	->setSerializer((new JsonSerializer())->add(new PrimitiveSerializer()));


$result = $tester->test($dq, 255);

var_dump($result);