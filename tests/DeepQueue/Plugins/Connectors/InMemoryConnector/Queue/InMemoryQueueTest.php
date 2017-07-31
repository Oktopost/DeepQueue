<?php
namespace DeepQueue\Plugins\Connectors\InMemoryConnector\Queue;


use DeepQueue\Scope;
use DeepQueue\Payload;
use DeepQueue\Manager\QueueConfig;
use DeepQueue\Plugins\Logger\Base\ILogger;

use PHPUnit\Framework\TestCase;

use Serialization\Serializers\JsonSerializer;
use Serialization\Json\Serializers\ArraySerializer;


class InMemoryQueueTest extends TestCase
{
	private function getSubject(): InMemoryQueue
	{
		$queue = new InMemoryQueue('inmemory', 
			(new JsonSerializer())->add(new ArraySerializer()), Scope::skeleton(ILogger::class));
		return $queue;
	}
	
	
	public function test_enqueue()
	{
		$queue = $this->getSubject();
		
		$payload1 = new Payload([1,2,3]);
		$payload1->Key = 'pay1';
		
		$payload2 = new Payload(['a', 'b', 'c']);
		$payload2->Key = 'pay2';
		
		$ids = $queue->enqueue([$payload1, $payload2]);
		
		self::assertEquals($payload1->Key, $ids[0]);
		self::assertEquals($payload2->Key, $ids[1]);
		
		$workloads = $queue->dequeueWorkload(2, null, new QueueConfig());
		
		self::assertEquals(2, sizeof($workloads));
	}
	
	public function test_dequeueWorkload_WithoutWait()
	{
		$queue = $this->getSubject();
		
		$payload1 = new Payload([1,2,3]);
		$payload1->Key = 'pay1';
		
		$payload2 = new Payload(['a', 'b', 'c']);
		$payload2->Key = 'pay2';
		
		$queue->enqueue([$payload1, $payload2]);
		
		$workloads = $queue->dequeueWorkload(2, null, new QueueConfig());
		
		self::assertEquals(2, sizeof($workloads));
		self::assertEquals(1, $workloads[0]->Payload[0]);
	}
	
	public function test_dequeueWorkload_CountZero()
	{
		$queue = $this->getSubject();
		
		$payload1 = new Payload([1,2,3]);
		$payload1->Key = 'pay1';
		
		$payload2 = new Payload(['a', 'b', 'c']);
		$payload2->Key = 'pay2';
		
		$queue->enqueue([$payload1, $payload2]);
		
		$workloads = $queue->dequeueWorkload(0, null, new QueueConfig());
		
		self::assertEquals(0, sizeof($workloads));
		
		$workloads = $queue->dequeueWorkload(2, null, new QueueConfig());
		
		self::assertEquals(2, sizeof($workloads));
		self::assertEquals($payload1->Key, $workloads[0]->Id);
	}
	
	public function test_dequeueWorkload_CountBelowZero()
	{
				$queue = $this->getSubject();
		
		$payload1 = new Payload([1,2,3]);
		$payload1->Key = 'pay1';
		
		$payload2 = new Payload(['a', 'b', 'c']);
		$payload2->Key = 'pay2';
		
		$queue->enqueue([$payload1, $payload2]);
		
		$workloads = $queue->dequeueWorkload(-1, null, new QueueConfig());
		
		self::assertEquals(0, sizeof($workloads));
		
		$workloads = $queue->dequeueWorkload(2, null, new QueueConfig());
		
		self::assertEquals(2, sizeof($workloads));
		self::assertEquals($payload1->Key, $workloads[0]->Id);
	}
	
	public function test_dequeueWorkload_WithWait()
	{
		$queue = $this->getSubject();
		
		$payload1 = new Payload([1,2,3]);
		$payload1->Key = 'pay1';
		
		$payload2 = new Payload(['a', 'b', 'c']);
		$payload2->Key = 'pay2';
		
		$queue->enqueue([$payload1, $payload2]);
		
		$workloads = $queue->dequeueWorkload(2, 1, new QueueConfig());
		
		self::assertEquals(2, sizeof($workloads));
		self::assertEquals(1, $workloads[0]->Payload[0]);
	}
	
	public function test_dequeueEmptyQueue_WithWait()
	{
		$queue = $this->getSubject();

		$workloads = $queue->dequeueWorkload(2, 1, new QueueConfig());
		
		self::assertEmpty($workloads);
	}
}