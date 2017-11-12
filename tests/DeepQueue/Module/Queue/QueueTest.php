<?php
namespace DeepQueue\Module\Queue;


use DeepQueue\Enums\QueueLoaderPolicy;
use DeepQueue\Payload;
use DeepQueue\Workload;
use DeepQueue\Base\Queue\IQueue;
use DeepQueue\Base\Queue\Remote\IRemoteQueue;
use DeepQueue\Plugins\Logger\Base\ILogger;
use DeepQueue\PreparedConfiguration\PreparedQueue;

use lib\TestLogProvider;
use lib\ThrowableQueueDecorator;

use PHPUnit\Framework\TestCase;

use Serialization\Json\Serializers\ArraySerializer;
use Serialization\Serializers\JsonSerializer;


class QueueTest extends TestCase
{
	private const QUEUE_NAME = 'test';
	
	
	/** @var TestLogProvider */
	private $dummyLogProvider = null;
	
	
	private function getSubject(): IQueue
	{
		$dq = PreparedQueue::InMemory((new JsonSerializer())->add(new ArraySerializer()));
		
		$dq->config()->setQueueNotExistsPolicy(QueueLoaderPolicy::CREATE_NEW);

		return $dq->get(self::QUEUE_NAME);
	}
	
	private function getThrowableQueue(?string $name = null): IQueue
	{
		$dq = PreparedQueue::InMemory((new JsonSerializer())->add(new ArraySerializer()));
		
		$dq->config()->addConnectorBuilder(ThrowableQueueDecorator::class);
		$dq->config()->setQueueNotExistsPolicy(QueueLoaderPolicy::CREATE_NEW);
		
		$this->dummyLogProvider = new TestLogProvider();
		
		$dq->config()->addLogProvider($this->dummyLogProvider);
		
		return $dq->get($name ?: self::QUEUE_NAME);
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

	
	public function setUp()
	{
		$dq = PreparedQueue::InMemory((new JsonSerializer())->add(new ArraySerializer()));
		$dq->manager(self::QUEUE_NAME)->clearQueue();
	}


	public function test_ConstructorPassRemoteQueue_NoException()
	{
		$remoteQueue = $this->getRemoteMock();

		$queue = new Queue(self::QUEUE_NAME, $remoteQueue, $this->getLoggerMock());

		self::assertInstanceOf(IQueue::class, $queue);
	}

	/**
	 * @expectedException \DeepQueue\Exceptions\UnexpectedDeepQueueException
	 */
	public function test_ConstructorPassWrongOneParam_Exception()
	{
		$remote = new \stdClass();

		$queue = new Queue(self::QUEUE_NAME, $remote, $this->getLoggerMock());

		self::assertInstanceOf(IQueue::class, $queue);
	}

	/**
	 * @expectedException \DeepQueue\Exceptions\UnexpectedDeepQueueException
	 */
	public function test_ConstructorPassWrongFirstParam_Exception()
	{
		$remote = new \stdClass();

		$enqueue = $this->getRemoteMock();

		$queue = new Queue(self::QUEUE_NAME, $remote, $this->getLoggerMock(), $enqueue);

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
		
		self::assertEquals($data1[0], $payloadData[$payload1->Key][0]);
		self::assertEquals($data2[0], $payloadData[$payload2->Key][0]);
	}
	
	public function test_EnqueueAll_KeyExistWithoutPayload_GetKey()
	{
		$queue = $this->getSubject();
		
		$key1 = 'test';
		$data1 = [1,2,3];
		
		$key2 = 'test2';
		$data2 = ['a', 'b', 'c'];
		
		$ids = $queue->enqueueAll(['test' => $data1, 'test2' => $data2]);
		
		self::assertEquals($key1, $ids[0]);
		self::assertEquals($key2, $ids[1]);
		
		$payloadData = $queue->dequeueWorkload(2);
		
		self::assertEquals($key1, $payloadData[0]->Id);
		self::assertEquals($key2, $payloadData[1]->Id);
		
		self::assertEquals($data1, $payloadData[0]->Payload);
		self::assertEquals($data2, $payloadData[1]->Payload);
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
	
	public function test_Enqueue_ThrowError_LogError()
	{
		$this->getThrowableQueue()->enqueue(new Payload('testdata'));
		self::assertEquals(self::QUEUE_NAME, $this->dummyLogProvider->logEntry->QueueName);
	}
	
	public function test_EnqueueAll_ThrowError_LogError()
	{
		$this->getThrowableQueue('test2')->enqueueAll([new Payload('testdata2')]);
		self::assertEquals('test2', $this->dummyLogProvider->logEntry->QueueName);
	}
	
	public function test_DequeueWorkload_ThrowError_LogError()
	{
		$this->getThrowableQueue('test3')->dequeueWorkload(128);
		self::assertEquals('test3', $this->dummyLogProvider->logEntry->QueueName);
	}
}