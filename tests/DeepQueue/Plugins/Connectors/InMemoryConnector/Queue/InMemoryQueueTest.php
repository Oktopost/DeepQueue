<?php
namespace DeepQueue\Plugins\Connectors\InMemoryConnector\Queue;


use DeepQueue\Scope;
use DeepQueue\Payload;
use DeepQueue\Plugins\Logger\Logger;
use DeepQueue\Plugins\Connectors\InMemoryConnector\Base\IInMemoryQueueStorage;

use PHPUnit\Framework\TestCase;

use Serialization\Json\Serializers\ArraySerializer;
use Serialization\Serializers\JsonSerializer;


class InMemoryQueueTest extends TestCase
{
	private function getSubject(): InMemoryQueue
	{
		$queue = new InMemoryQueue('inmemory', 
			(new JsonSerializer())->add(new ArraySerializer()), Logger::instance());
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
		
		$workloads = $queue->dequeueWorkload(2);
		
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
		
		$workloads = $queue->dequeueWorkload(2);
		
		self::assertEquals(2, sizeof($workloads));
		self::assertEquals(1, $workloads[0]->Payload[0]);
	}
	
	public function test_dequeueWorkload_WithWait()
	{
		$queue = $this->getSubject();
		
		$payload1 = new Payload([1,2,3]);
		$payload1->Key = 'pay1';
		
		$payload2 = new Payload(['a', 'b', 'c']);
		$payload2->Key = 'pay2';
		
		$queue->enqueue([$payload1, $payload2]);
		
		$workloads = $queue->dequeueWorkload(2, 1);
		
		self::assertEquals(2, sizeof($workloads));
		self::assertEquals(1, $workloads[0]->Payload[0]);
	}
	
	public function test_dequeueEmptyQueue_WithWait()
	{
		$queue = $this->getSubject();

		$workloads = $queue->dequeueWorkload(2, 1);
		
		self::assertEmpty($workloads);
	}
	
	public function test_dequeueAlreadyDequeuedWorkload_GetEmptyArray()
	{
		$queue = $this->getSubject();
		
		$payload1 = new Payload([1,2,3]);
		$payload1->Key = 'pay1';
		
		$queue->enqueue([$payload1]);
		
		/** @var IInMemoryQueueStorage $storage */
		$storage = Scope::skeleton(IInMemoryQueueStorage::class);
		$storage->deletePayload('inmemory', $payload1->Key);
		
		$workloads = $queue->dequeueWorkload(1);
		
		self::assertEmpty($workloads);
	}
}