<?php
namespace DeepQueue;


use DeepQueue\Enums\QueueLoaderPolicy;
use DeepQueue\Plugins\InMemoryConnector\InMemoryConnector;
use DeepQueue\Plugins\InMemoryManager\InMemoryManager;
use PHPUnit\Framework\TestCase;
use Serialization\Json\Serializers\ArraySerializer;
use Serialization\Json\Serializers\PrimitiveSerializer;
use Serialization\Serializers\JsonSerializer;


class DeepQueueTest extends TestCase
{
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
	}
}