<?php
namespace DeepQueue\Module\Queue;


use DeepQueue\Payload;
use DeepQueue\Plugins\Logger\Base\ILogger;
use DeepQueue\Plugins\Logger\Logger;
use DeepQueue\Workload;
use DeepQueue\Base\Queue\IQueue;
use DeepQueue\Base\Queue\Remote\IRemoteQueue;
use DeepQueue\PreparedConfiguration\PreparedQueue;

use PHPUnit\Framework\TestCase;

use Serialization\Json\Serializers\ArraySerializer;
use Serialization\Serializers\JsonSerializer;


class QueueTest extends TestCase
{
	private function getSubject(): IQueue
	{
		$dq = PreparedQueue::InMemory((new JsonSerializer())->add(new ArraySerializer()));

		return $dq->get('test');
	}
	
	/**
	 * @return \PHPUnit_Framework_MockObject_MockObject|ILogger
	 */
	private function getLoggerMock(): ILogger
	{
		$logger = $this->createMock(ILogger::class);
		return $logger;
	}

	/**
	 * @return \PHPUnit_Framework_MockObject_MockObject|IRemoteQueue
	 */
	private function getRemoteMock(): IRemoteQueue
	{
		$remote = $this->createMock(IRemoteQueue::class);
		return $remote;
	}


	public function test_ConstructorPassRemoteQueue_NoException()
	{
		$remoteQueue = $this->getRemoteMock();

		$queue = new Queue('test', $remoteQueue, $this->getLoggerMock());

		self::assertInstanceOf(IQueue::class, $queue);
	}

	/**
	 * @expectedException \DeepQueue\Exceptions\UnexpectedDeepQueueException
	 */
	public function test_ConstructorPassWrongOneParam_Exception()
	{
		$remote = new \stdClass();

		$queue = new Queue('test', $remote, $this->getLoggerMock());

		self::assertInstanceOf(IQueue::class, $queue);
	}

	/**
	 * @expectedException \DeepQueue\Exceptions\UnexpectedDeepQueueException
	 */
	public function test_ConstructorPassWrongFirstParam_Exception()
	{
		$remote = new \stdClass();

		$enqueue = $this->getRemoteMock();

		$queue = new Queue('test', $remote, $this->getLoggerMock(), $enqueue);

		self::assertInstanceOf(IQueue::class, $queue);
	}

	public function test_DequeueWorkload_GetWorkloadArray()
	{
		$queue = $this->getSubject();
		
		$data1 = [1,2,3];
		$data2 = ['a', 'b', 'c'];
		
		$payload1 = new Payload($data1);
		$payload2 = new Payload($data2);
		
		$queue->enqueueAll([$payload1, $payload2]);
		
		$workload = $queue->dequeueWorkload(2);
		
		self::assertEquals(2, sizeof($workload));
		self::assertEquals($data1[0], $workload[0]->Payload[0]);
		self::assertEquals($data2[0], $workload[1]->Payload[0]);
	}
	
	public function test_DequeueOnce_GetPayloadData()
	{
		$queue = $this->getSubject();
		
		$data1 = [1,2,3];
		
		$payload1 = new Payload($data1);
		
		$queue->enqueueAll([$payload1]);
		
		$payloadData = $queue->dequeueOnce();
		
		self::assertEquals($data1[0], $payloadData[0]);
	}
	
	public function test_DequeueWorkloadOnce_GetWorkload()
	{
		$queue = $this->getSubject();
		
		$data1 = [1,2,3];
		
		$payload1 = new Payload($data1);
		
		$queue->enqueueAll([$payload1]);
		
		$workload = $queue->dequeueWorkloadOnce();
		
		self::assertInstanceOf(Workload::class, $workload);
		self::assertEquals($data1[0], $workload->Payload[0]);
	}
	
	public function test_Dequeue_GetPayloadDataArray()
	{
		$queue = $this->getSubject();
		
		$data1 = [1,2,3];
		$data2 = ['a', 'b', 'c'];
		
		$payload1 = new Payload($data1);
		$payload2 = new Payload($data2);
		
		$queue->enqueueAll([$payload1, $payload2]);
		
		$payloadDatas = $queue->dequeue(20);
		
		self::assertEquals(2, sizeof($payloadDatas));
		self::assertEquals($data1[0], $payloadDatas[0][0]);
		self::assertEquals($data2[0], $payloadDatas[1][0]);
	}
	
	public function test_Enqueue_KeyExist_GetKey()
	{
		$queue = $this->getSubject();
		
		$data1 = [1,2,3];
		
		$payload1 = new Payload($data1);
		$payload1->Key = 'test';
		
		$id = $queue->enqueue($payload1);
		
		self::assertEquals($payload1->Key, $id);
		
		$payloadData = $queue->dequeueOnce();
		
		self::assertEquals($data1[0], $payloadData[0]);
	}
	
	public function test_Enqueue_NotWorkload_KeyExist_GetKey()
	{
		$queue = $this->getSubject();
		
		$data1 = [1,2,3];

		$id = $queue->enqueue($data1, 'test');
		
		self::assertEquals('test', $id);
		
		$payloadData = $queue->dequeueOnce();
		
		self::assertEquals($data1[0], $payloadData[0]);
	}
	
	public function test_Enqueue_NoKeyExist_GetNewKey()
	{
		$queue = $this->getSubject();
		
		$data1 = [1,2,3];
		
		$payload1 = new Payload($data1);
		
		$id = $queue->enqueue($payload1);
		
		self::assertNotEmpty($id);
		
		$payloadData = $queue->dequeueOnce();
		
		self::assertEquals($data1[0], $payloadData[0]);
	}
	
	public function test_Enqueue_NotWorkload_NoKeyExist_GetNewKey()
	{
		$queue = $this->getSubject();
		
		$data1 = [1,2,3];

		$id = $queue->enqueue($data1);
		
		self::assertNotEmpty($id);
		
		$payloadData = $queue->dequeueOnce();
		
		self::assertEquals($data1[0], $payloadData[0]);
	}
		
	public function test_EnqueueAll_KeyExist_GetKey()
	{
		$queue = $this->getSubject();
		
		$data1 = [1,2,3];
		$data2 = ['a', 'b', 'c'];
		
		$payload1 = new Payload($data1);
		$payload1->Key = 'test';
		
		$payload2 = new Payload($data2);
		$payload2->Key = 'test2';
		
		$ids = $queue->enqueueAll([$payload1, $payload2]);
		
		self::assertEquals($payload1->Key, $ids[0]);
		self::assertEquals($payload2->Key, $ids[1]);
		
		$payloadData = $queue->dequeue(2);
		
		self::assertEquals($data1[0], $payloadData[0][0]);
		self::assertEquals($data2[0], $payloadData[1][0]);
	}
	
	public function test_EnqueueAll_NoKeyExist_GetNewKey()
	{
		$queue = $this->getSubject();
		
		$data1 = [1,2,3];
		$data2 = ['a', 'b', 'c'];
		
		$payload1 = new Payload($data1);
		
		$payload2 = new Payload($data2);
		
		$ids = $queue->enqueueAll([$payload1, $payload2]);
		
		self::assertNotEmpty($ids[0]);
		self::assertNotEmpty($ids[1]);
		
		$payloadData = $queue->dequeue(2);
		
		self::assertEquals($data1[0], $payloadData[0][0]);
		self::assertEquals($data2[0], $payloadData[1][0]);
	}
	
	public function test_EnqueueAll_NoKeyExist_DequeueSameKeys()
	{
		$queue = $this->getSubject();
		
		$data1 = [1,2,3];
		$data2 = ['a', 'b', 'c'];
		
		$payload1 = new Payload($data1);
		
		$payload2 = new Payload($data2);
		
		$ids = $queue->enqueueAll([$payload1, $payload2]);
		
		$workloads = $queue->dequeueWorkload(2);
		
		self::assertEquals($ids[0], $workloads[0]->Id);
		self::assertEquals($ids[1], $workloads[1]->Id);
	}
	
	public function test_EnqueueAll_NotWorkload_NoKeyExist_DequeueSameKeys()
	{
		$queue = $this->getSubject();
		
		$data1 = [1,2,3];
		$data2 = ['a', 'b', 'c'];

		$ids = $queue->enqueueAll([$data1, $data2]);
		
		$workloads = $queue->dequeueWorkload(2);
		
		self::assertEquals($ids[0], $workloads[0]->Id);
		self::assertEquals($ids[1], $workloads[1]->Id);
	}
	
	public function test_EnqueueAll_NotWorkload_SetDelay()
	{
		$queue = $this->getSubject();
		
		$data1 = [1,2,3];
		$data2 = ['a', 'b', 'c'];

		$ids = $queue->enqueueAll([$data1, $data2], 3);
		
		$workloads = $queue->dequeueWorkload(2);
		
		self::assertEquals($ids[0], $workloads[0]->Id);
		self::assertEquals($ids[1], $workloads[1]->Id);
	}
}