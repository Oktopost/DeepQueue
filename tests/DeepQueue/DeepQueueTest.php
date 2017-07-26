<?php
namespace DeepQueue;


use DeepQueue\Base\Plugins\ConnectorElements\IQueueManager;
use DeepQueue\Enums\Policy;
use DeepQueue\Enums\QueueLoaderPolicy;
use DeepQueue\Plugins\Managers\InMemoryManager\InMemoryManager;
use DeepQueue\Plugins\Connectors\RedisConnector\RedisConnector;
use DeepQueue\Plugins\Connectors\InMemoryConnector\InMemoryConnector;

use DeepQueue\Utils\RedisConfigParser;
use PHPUnit\Framework\TestCase;

use Predis\Client;

use Serialization\Json\Serializers\ArraySerializer;
use Serialization\Json\Serializers\PrimitiveSerializer;
use Serialization\Serializers\JsonSerializer;


class DeepQueueTest extends TestCase
{
	private function getRedisClient(): Client
	{
		$config = RedisConfigParser::parse(['prefix' => 'sanity.test']);
		
		return new Client($config->getParameters(), $config->getOptions());
	}
	
	
	public function setUp()
	{
		$this->getRedisClient()->eval("return redis.call('del', 'defaultKey', unpack(redis.call('keys', ARGV[1])))", 
			0, 'sanity.test:*');
	}
	
	
	public function test_sanity()
	{
		$dq = new DeepQueue();
		
		$dq->config()
			->setManagerPlugin(new InMemoryManager())
			->setQueueNotExistsPolicy(QueueLoaderPolicy::CREATE_NEW)
			->setConnectorPlugin(new InMemoryConnector())
			->setSerializer((new JsonSerializer())->add(new ArraySerializer())->add(new PrimitiveSerializer()));
		
		$queueObject = $dq->getQueueObject('test');
		
		$paylodaData = new \stdClass();
		$paylodaData->Data = [1,2,3];
		
		$payload = new Payload($paylodaData);
		$payload->Key = 'test-payload';
		
		$queue = $dq->get('test');
		
		$queue->enqueue($payload);
		
		$queueObjectMetaData = $queueObject->getMetaData();
		
		$queueMetaData = $dq->getMetaData('test');
		
		self::assertEquals(1, $queueMetaData->Enqueued);
		self::assertEquals($queueObjectMetaData->Enqueued, $queueMetaData->Enqueued);
		self::assertTrue($queueMetaData->hasEnqueued());
		self::assertFalse($queueMetaData->hasDelayed());
		
		$workload = $queue->dequeueWorkload(1);

		self::assertInstanceOf(Workload::class, $workload[0]);

		self::assertEquals(3, sizeof($workload[0]->Payload->Data));
		
		$workload = $queue->dequeueWorkload(1);
		
		self::assertEmpty($workload);
		
		$queue->enqueue($payload);
		$queue->enqueue($payload);
		
		self::assertEquals(1, $queueObject->getMetaData()->Enqueued);
		
		$payload2 = new Payload();
		$payload2->Key = 'test2-payload';
		$payload2->Payload = 'test';
		
		$queue2 = $dq->get('test');
		
		$queue2->enqueue($payload2);
		
		self::assertEquals(2, $queueObject->getMetaData()->Enqueued);
		
		$workload2 = $queue2->dequeueWorkload(2);
		
		self::assertEquals('test2-payload', $workload2[1]->Id);
		
		
		$testPayload = new Payload('777');
		
		$queue->dequeue(256);
		
		$queue->enqueue($testPayload);
		
		self::assertEquals('777', $queueObject->getStream()->dequeue(1)[0]);
		
		$queue->enqueue($testPayload);
		
		self::assertInstanceOf(IQueueManager::class, $dq->manager($queueObject->Name));
		
		$dq->manager($queueObject->Name)->clearQueue();
		
		self::assertFalse($dq->manager($queueObject->Name)->getMetaData()->hasEnqueued());
	}
	
	public function test_sanity_withNullPayload()
	{
		$dq = new DeepQueue();
		
		$dq->config()
			->setManagerPlugin(new InMemoryManager())
			->setQueueNotExistsPolicy(QueueLoaderPolicy::CREATE_NEW)
			->setConnectorPlugin(new InMemoryConnector())
			->setSerializer((new JsonSerializer())->add(new ArraySerializer())->add(new PrimitiveSerializer()));
		
		$queue = $dq->get('nullTest');
		
		$payload = new Payload();
		$payload->Key = 'nullkey';
		
		self::assertFalse($payload->hasPayload());
		
		$queue->enqueue($payload);
		
		$workload = $queue->dequeueWorkload(1);
		
		self::assertEquals($payload->Key, $workload[0]->Id);
		self::assertNull($workload[0]->Payload);
	}
	
	public function test_sanity_WithRedisAndWaiting()
	{
		$dq = new DeepQueue();
		
		$dq->config()
			->setManagerPlugin(new InMemoryManager())
			->setQueueNotExistsPolicy(QueueLoaderPolicy::CREATE_NEW)
			->setConnectorPlugin(new RedisConnector(['prefix' => 'sanity.test']))
			->setSerializer((new JsonSerializer())->add(new ArraySerializer())->add(new PrimitiveSerializer()));
		
		$queueObject = $dq->getQueueObject('redis.test.queue');
		$queueObject->Config->DelayPolicy = Policy::ALLOWED;
		$queueObject->Config->MaximalDelay = 30;
		
		$dq->config()->manager()->update($queueObject);
		
		$queue = $dq->get('redis.test.queue');
		
		$payload = new Payload([1,2,3]);
		$payload->Key = 'un-delayed-payload';
		
		$delayedPayload = new Payload('payload-data');
		$delayedPayload->Delay = 5;
		
		$longDelayedPayload = new Payload();
		$longDelayedPayload->Key = 'long-delayed-payload';
		$longDelayedPayload->Delay = 15;
		
		$queue->enqueueAll([$payload, $delayedPayload, $longDelayedPayload]);
		
		$workloads = $queue->dequeueWorkload(255, 5);
		
		self::assertEquals(1, sizeof($workloads));
		self::assertEquals($payload->Key, $workloads[0]->Id);
		
		$payloads = $queue->dequeue(255, 5);
		
		self::assertEquals(1, sizeof($payloads));
		self::assertEquals($delayedPayload->Payload, $payloads[0]);
	}
}