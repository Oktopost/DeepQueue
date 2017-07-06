<?php
namespace DeepQueue\Plugins\Connectors\MySQLConnector\Queue;


use DeepQueue\Payload;
use DeepQueue\Plugins\Logger\Base\ILogger;
use DeepQueue\Plugins\Connectors\MySQLConnector\Converter\MySQLPayloadConverter;
use DeepQueue\Plugins\Connectors\MySQLConnector\DAO\MySQLQueueDAO;
use DeepQueue\Plugins\Connectors\MySQLConnector\Base\DAO\IMySQLQueueDAO;

use lib\MySQLConfig;

use PHPUnit\Framework\TestCase;

use Serialization\Serializers\JsonSerializer;
use Serialization\Json\Serializers\ArraySerializer;
use Serialization\Json\Serializers\PrimitiveSerializer;


class MySQLQueueTest extends TestCase
{
	private const PAYLOADS_TABLE_NAME 	= 'DeepQueuePayload';
	private const ENQUEUE_TABLE_NAME	= 'DeepQueueEnqueue';
	private const TEST_QUEUE_NAME		= 'queue.test.queue';
	
	
	private function getDAO():IMySQLQueueDAO
	{
		$dao = new MySQLQueueDAO();
		$dao->initConnector(MySQLConfig::get());
		
		return $dao;
	}
	
	/**
	 * @return \PHPUnit_Framework_MockObject_MockObject|ILogger
	 */
	private function getLoggerMock(): ILogger
	{
		$logger = $this->createMock(ILogger::class);
		return $logger;
	}
	
	private function getSubject(): MySQLQueue
	{
		return new MySQLQueue(self::TEST_QUEUE_NAME, $this->getDAO(), 
			(new JsonSerializer())->add(new PrimitiveSerializer())->add(new ArraySerializer()),
			$this->getLoggerMock());
	}
	
	private function getEnqueuedIds($isDelayed = false): array 
	{
		$queue = MySQLConfig::connector()
			->select()
			->column('Id')
			->from(self::ENQUEUE_TABLE_NAME)
			->byField('QueueName', self::TEST_QUEUE_NAME);
		
		$now = date('Y-m-d H:i:s');	
			
		if ($isDelayed)
		{
			$queue->where('DequeueTime > ?', $now);
		}
		else
		{
			$queue->where('DequeueTime <= ?', $now);
		}
			
		return $queue->queryColumn();
	}
	
	private function preparePayloads(array $payloads): array
	{
		$converter = new MySQLPayloadConverter((new JsonSerializer())
			->add(new PrimitiveSerializer())
			->add(new ArraySerializer()));
		
		return $converter->prepareAll(self::TEST_QUEUE_NAME, $payloads);
	}
	
	
	protected function setUp()
	{
		MySQLConfig::connector()->delete()
			->where('1 = 1')
			->from(self::PAYLOADS_TABLE_NAME)
			->executeDml();
		
		MySQLConfig::connector()->delete()
			->where('1 = 1')
			->from(self::ENQUEUE_TABLE_NAME)
			->executeDml();
	}
	
	
	public function test_enqueue_EmptyPayloads_GetEmptyArray()
	{
		$ids = $this->getSubject()->enqueue([]);
		
		self::assertEmpty($ids);
	}
	
	public function test_enqueue_NotEmptyPayloads_GetIdsArray()
	{
		$payload1 = new Payload();
		$payload1->Key = 'p1';
		
		$payload2 = new Payload();
		$payload2->Key = 'p2';
		
		$ids = $this->getSubject()->enqueue([$payload1, $payload2]);
		
		self::assertEquals(2, sizeof($ids));
		self::assertEquals($payload1->Key, $ids[0]);
	}
	
	public function test_dequeue_Empty_WithoutWaiting_GetEmptyArray()
	{
		self::assertEmpty($this->getSubject()->dequeueWorkload(255, 0));
	}
	
	public function test_dequeue_Empty_WithWaiting_GetEmptyArray()
	{
		self::assertEmpty($this->getSubject()->dequeueWorkload(255, 2));
	}
	
	public function test_dequeue_Now_WithoutWaiting_GetPayloads()
	{
		$payload1 = new Payload();
		$payload1->Key = 'now1';
		$payload1->Payload = 'payload1';
		
		$payload2 = new Payload();
		$payload2->Key = 'now2';
		
		$payload3 = new Payload();
		$payload3->Key = 'now3';
		
		$payload4 = new Payload();
		$payload4->Key = 'now4';
		
		$prepared = $this->preparePayloads([$payload1, $payload2, $payload3, $payload4]);
		$this->getDAO()->enqueue(self::TEST_QUEUE_NAME, $prepared);
		
		$workloads = $this->getSubject()->dequeueWorkload(3, 0);
		
		self::assertEquals(3, sizeof($workloads));
		self::assertEquals($payload1->Key, $workloads[0]->Id);
		
		$leftIds = $this->getEnqueuedIds();
		
		self::assertEquals(1, sizeof($leftIds));
		self::assertEquals($payload4->Key, $leftIds[0]);
	}
	
	public function test_dequeue_Now_WithWaiting_GetPayloads()
	{
		$payload1 = new Payload();
		$payload1->Key = 'now1';
		$payload1->Payload = 'payload1';
		
		$payload2 = new Payload();
		$payload2->Key = 'now2';
		
		$payload3 = new Payload();
		$payload3->Key = 'now3';
		
		$payload4 = new Payload();
		$payload4->Key = 'now4';
		
		$prepared = $this->preparePayloads([$payload1, $payload2, $payload3, $payload4]);
		$this->getDAO()->enqueue(self::TEST_QUEUE_NAME, $prepared);
		
		$workloads = $this->getSubject()->dequeueWorkload(3, 3);
		
		self::assertEquals(3, sizeof($workloads));
		self::assertEquals($payload1->Key, $workloads[0]->Id);
		
		$leftIds = $this->getEnqueuedIds();
		
		self::assertEquals(1, sizeof($leftIds));
		self::assertEquals($payload4->Key, $leftIds[0]);
	}
	
	public function test_dequeue_Delayed_WithWaiting_GetPayloads()
	{
		$payload1 = new Payload();
		$payload1->Key = 'd1';
		$payload1->Payload = 'payload1';
		$payload1->Delay = 1;
		
		$payload2 = new Payload();
		$payload2->Key = 'd2';
		$payload2->Delay = 2;
		
		$payload3 = new Payload();
		$payload3->Key = 'd3';
		$payload3->Delay = 3;
		
		$payload4 = new Payload();
		$payload4->Key = 'd4';
		$payload4->Delay = 5;
		
		$prepared = $this->preparePayloads([$payload1, $payload2, $payload3, $payload4]);
		$this->getDAO()->enqueue(self::TEST_QUEUE_NAME, $prepared);
		
		$workloads = $this->getSubject()->dequeueWorkload(3, 3);
		
		self::assertEquals(3, sizeof($workloads));
		self::assertEquals($payload1->Key, $workloads[0]->Id);
		
		$leftIds = $this->getEnqueuedIds();
		
		self::assertEmpty($leftIds);
		
		$delayIds = $this->getEnqueuedIds(true);
		
		self::assertEquals(1, sizeof($delayIds));
		self::assertEquals($payload4->Key, $delayIds[0]);
	}
	
	public function test_dequeue_Delayed_WithoutWaiting_GetEmptyArray()
	{
		$payload1 = new Payload();
		$payload1->Key = 'd1';
		$payload1->Payload = 'payload1';
		$payload1->Delay = 1;
		
		$payload2 = new Payload();
		$payload2->Key = 'd2';
		$payload2->Delay = 2;
		
		$prepared = $this->preparePayloads([$payload1, $payload2]);
		$this->getDAO()->enqueue(self::TEST_QUEUE_NAME, $prepared);
		
		$workloads = $this->getSubject()->dequeueWorkload(3, 0);
		
		self::assertEmpty($workloads);
				
		$leftIds = $this->getEnqueuedIds();
		
		self::assertEmpty($leftIds);
		
		$delayIds = $this->getEnqueuedIds(true);
		
		self::assertEquals(2, sizeof($delayIds));
		self::assertEquals($payload1->Key, $delayIds[0]);
	}
	
	public function test_dequeue_Delayed_WithoutWaitingAndWithSleep_GetPayloads()
	{
		$payload1 = new Payload();
		$payload1->Key = 'd1';
		$payload1->Payload = 'payload1';
		$payload1->Delay = 1;
		
		$payload2 = new Payload();
		$payload2->Key = 'd2';
		$payload2->Delay = 2;
		
		$prepared = $this->preparePayloads([$payload1, $payload2]);
		$this->getDAO()->enqueue(self::TEST_QUEUE_NAME, $prepared);
		
		sleep(1);
		
		$workloads = $this->getSubject()->dequeueWorkload(3, 0);
		
		self::assertEquals(1, sizeof($workloads));
				
		$leftIds = $this->getEnqueuedIds();
		
		self::assertEmpty($leftIds);
		
		$delayIds = $this->getEnqueuedIds(true);
		
		self::assertEquals(1, sizeof($delayIds));
		self::assertEquals($payload2->Key, $delayIds[0]);
	}
	
	public function test_dequeue_NowAndDelayed_WithWaiting_GetNowPayloads()
	{
		$payload1 = new Payload();
		$payload1->Key = 'now1';
		$payload1->Payload = 'payload1';
		
		$payload2 = new Payload();
		$payload2->Key = 'now2';
		
		$payload3 = new Payload();
		$payload3->Key = 'd1';
		$payload3->Payload = 'payload1';
		$payload3->Delay = 1;
		
		$payload4 = new Payload();
		$payload4->Key = 'd2';
		$payload4->Delay = 2;
		
		$prepared = $this->preparePayloads([$payload1, $payload2, $payload3, $payload4]);
		$this->getDAO()->enqueue(self::TEST_QUEUE_NAME, $prepared);
		
		$workloads = $this->getSubject()->dequeueWorkload(4, 5);
		
		self::assertEquals(2, sizeof($workloads));
		self::assertEquals($payload1->Key, $workloads[0]->Id);
				
		$leftIds = $this->getEnqueuedIds();
		
		self::assertEmpty($leftIds);
		
		$delayIds = $this->getEnqueuedIds(true);
		
		self::assertEquals(2, sizeof($delayIds));
		self::assertEquals($payload3->Key, $delayIds[0]);
	}
	
	public function test_dequeue_NowAndDelayed_WithSleep_GetNowAndDelayed()
	{
		$payload1 = new Payload();
		$payload1->Key = 'now1';
		$payload1->Payload = 'payload1';
		
		$payload2 = new Payload();
		$payload2->Key = 'now2';
		
		$payload3 = new Payload();
		$payload3->Key = 'd1';
		$payload3->Payload = 'payload1';
		$payload3->Delay = 1;
		
		$payload4 = new Payload();
		$payload4->Key = 'd2';
		$payload4->Delay = 2;
		
		$payload5 = new Payload();
		$payload5->Key = 'd3';
		$payload5->Delay = 5;
		
		$prepared = $this->preparePayloads([$payload1, $payload2, $payload3, $payload4, $payload5]);
		$this->getDAO()->enqueue(self::TEST_QUEUE_NAME, $prepared);
		
		sleep(4);
		
		$workloads = $this->getSubject()->dequeueWorkload(255, 5);
				
		self::assertEquals(4, sizeof($workloads));
		self::assertEquals($payload3->Key, $workloads[0]->Id);
				
		$leftIds = $this->getEnqueuedIds();
		
		self::assertEmpty($leftIds);
		
		$delayIds = $this->getEnqueuedIds(true);
		
		self::assertEquals(1, sizeof($delayIds));
		self::assertEquals($payload5->Key, $delayIds[0]);
	}
}