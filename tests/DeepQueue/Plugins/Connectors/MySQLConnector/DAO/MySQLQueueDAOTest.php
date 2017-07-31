<?php
namespace DeepQueue\Plugins\Connectors\MySQLConnector\DAO;


use DeepQueue\Payload;
use DeepQueue\Plugins\Connectors\MySQLConnector\Converter\MySQLPayloadConverter;

use lib\MySQLConfig;

use PHPUnit\Framework\TestCase;

use Serialization\Serializers\JsonSerializer;
use Serialization\Json\Serializers\ArraySerializer;
use Serialization\Json\Serializers\PrimitiveSerializer;


class MySQLQueueDAOTest extends TestCase
{
	private const PAYLOADS_TABLE_NAME 	= 'DeepQueuePayload';
	private const ENQUEUE_TABLE_NAME	= 'DeepQueueEnqueue';
	private const TEST_QUEUE_NAME		= 'dao.test.queue';
	
	private function getSubject(): MySQLQueueDAO
	{
		$dao = new MySQLQueueDAO();
		$dao->initConnector(MySQLConfig::get());
		
		return $dao;
	}
	
	private function preparePayloads(array $payloads): array
	{
		$converter = new MySQLPayloadConverter((new JsonSerializer())
			->add(new PrimitiveSerializer())
			->add(new ArraySerializer()));
		
		return $converter->prepareAll(self::TEST_QUEUE_NAME, $payloads);
	}
	
	private function getEnqueuedIds(): array 
	{
		return MySQLConfig::connector()
			->select()
			->column('Id')
			->from(self::ENQUEUE_TABLE_NAME)
			->byField('QueueName', self::TEST_QUEUE_NAME)
			->queryColumn();
	}

	private function getPayloads(): array
	{
		return MySQLConfig::connector()
			->select()
			->from(self::PAYLOADS_TABLE_NAME)
			->byField('QueueName', self::TEST_QUEUE_NAME)
			->queryAll(true);
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
	
	
	public function test_initConnector_withIMySQLConnectorInstance()
	{
		$config = MySQLConfig::get();
		
		$sql = \Squid::MySql();
		$sql->config()->setConfig($config);

		$dao = new MySQLQueueDAO();
		$dao->initConnector($sql->getConnector());
		
		$payloads = $dao->dequeue(self::TEST_QUEUE_NAME, 255);
		
		self::assertEmpty($payloads);
	}
	
	public function test_enqueue_EmptyPayloads_ReturnEmptyArray()
	{
		$ids = $this->getSubject()->enqueue(self::TEST_QUEUE_NAME, []);
		
		self::assertEmpty($ids);
	}
	
	public function test_enqueue_AddPayloads_PayloadsExist()
	{
		$payload1 = new Payload();
		$payload1->Key = 'now1';
		$payload1->Payload = 'payload1';
		
		$payload2 = new Payload();
		$payload2->Key = 'd1';
		$payload2->Delay = 3;
		
		$payloads = $this->preparePayloads([$payload1, $payload2]);
		
		$ids = $this->getSubject()->enqueue(self::TEST_QUEUE_NAME, $payloads);
		
		self::assertEquals(2, sizeof($ids));
		self::assertEquals($payload1->Key, $ids[0]);
		
		$enqueueIds = $this->getEnqueuedIds();
		
		$payloads = $this->getPayloads();
		
		self::assertEquals(2, sizeof($enqueueIds));
		self::assertEquals(2, sizeof($payloads));
		
		self::assertTrue(in_array($payload1->Key, $enqueueIds));
		self::assertTrue(in_array($payload2->Key, $enqueueIds));
		
		self::assertTrue(strpos($payloads[1]['Payload'], $payload1->Payload) !== false);
	}
	
	public function test_dequeue_NoPayloads_ReturnEmptyArray()
	{
		$payloads = $this->getSubject()->dequeue(self::TEST_QUEUE_NAME, 255);
		
		self::assertEmpty($payloads);
	}
	
	public function test_dequeue_PayloadsExist_ReturnArray()
	{
		$payload1 = new Payload();
		$payload1->Key = 'now1';
		$payload1->Payload = 'payload1';
		
		$payload2 = new Payload();
		$payload2->Key = 'now2';
		
		$payload3 = new Payload();
		$payload3->Key = 'd1';
		$payload3->Delay = 2;
		
		$payload4 = new Payload();
		$payload4->Key = 'd2';
		$payload4->Delay = 15;
		
		$payloads = $this->preparePayloads([$payload1, $payload2, $payload3, $payload4]);
		$this->getSubject()->enqueue(self::TEST_QUEUE_NAME, $payloads);
		
		sleep(3);
		
		$payloads = $this->getSubject()->dequeue(self::TEST_QUEUE_NAME, 255);
		
		self::assertEquals(3, sizeof($payloads));
		
		$leftIds = $this->getEnqueuedIds();
		
		self::assertEquals(1, sizeof($leftIds));
		self::assertEquals($payload4->Key, $leftIds[0]);
	}
	
	public function test_countEnqueued_NoEnqueued_ReturnZero()
	{
		self::assertEquals(0, $this->getSubject()->countEnqueued(self::TEST_QUEUE_NAME));
	}
	
	public function test_countEnqueued_EnqueuedExist_ReturnAmount()
	{
		$payload1 = new Payload();
		$payload1->Key = 'now1';
		$payload1->Payload = 'payload1';

		$payload3 = new Payload();
		$payload3->Key = 'd1';
		
		$payloads = $this->preparePayloads([$payload1, $payload3]);
		$this->getSubject()->enqueue(self::TEST_QUEUE_NAME, $payloads);
		
		self::assertEquals(2, $this->getSubject()->countEnqueued(self::TEST_QUEUE_NAME));
	}
	
	public function test_countDelayed_NoDelayed_ReturnZero()
	{
		self::assertEquals(0, $this->getSubject()->countDelayed(self::TEST_QUEUE_NAME));
	}
	
	public function test_countDelayed_DelayedExist_ReturnAmount()
	{
		$payload1 = new Payload();
		$payload1->Key = 'now1';
		$payload1->Payload = 'payload1';

		$payload3 = new Payload();
		$payload3->Key = 'd1';
		$payload3->Delay = 5;
		
		$payloads = $this->preparePayloads([$payload1, $payload3]);
		$this->getSubject()->enqueue(self::TEST_QUEUE_NAME, $payloads);
		
		self::assertEquals(1, $this->getSubject()->countDelayed(self::TEST_QUEUE_NAME));
	}
	
	public function test_NotUpdateExistingDelayedTime_ReturnPayload()
	{
		$payload1 = new Payload();
		$payload1->Key = 'd1';
		$payload1->Payload = 'payload1';
		$payload1->Delay = 2;
		
		$payloads = $this->preparePayloads([$payload1]);
		
		$this->getSubject()->enqueue(self::TEST_QUEUE_NAME, $payloads);
		
		sleep(3);
		
		$this->getSubject()->enqueue(self::TEST_QUEUE_NAME, $payloads);
				
		$payloads = $this->getSubject()->dequeue(self::TEST_QUEUE_NAME, 1);
		
		self::assertNotEmpty($payloads);
		self::assertEquals($payload1->Key, $payloads[0]['Id']);
	}
	
	public function test_DequeueAll_GetOnePayloadManyEnqueued_GetOneOtherExistsAfter()
	{
		$payload1 = new Payload();
		$payload1->Key = 'n1';
		$payload1->Delay = 0;
		
		$payloads = $this->preparePayloads([$payload1]);
		$this->getSubject()->enqueue(self::TEST_QUEUE_NAME, $payloads);
		
		sleep(1);
		
		$payload2 = new Payload();
		$payload2->Key = 'n2';
		$payload2->Delay = 0;
		
		$payload3 = new Payload();
		$payload3->Key = 'n3';
		$payload3->Delay = 0;
		
		$payloads = $this->preparePayloads([$payload2, $payload3]);
		
		$this->getSubject()->enqueue(self::TEST_QUEUE_NAME, $payloads);
		
		$workload = $this->getSubject()->dequeue(self::TEST_QUEUE_NAME, 1);
		
		self::assertEquals(1, sizeof($workload));
		self::assertEquals($payload1->Key, $workload[0]['Id']);
		
		$workloads = $this->getSubject()->dequeue(self::TEST_QUEUE_NAME, 2);
		
		self::assertEquals(2, sizeof($workloads));
		
		self::assertEquals($payload2->Key, $workloads[0]['Id']);
		self::assertEquals($payload3->Key, $workloads[1]['Id']);
	}
	
	public function test_DequeueAll_CountZero_GetEmptyArray_OtherPayloadsStayInPlace()
	{
		$payload1 = new Payload();
		$payload1->Key = 'n1';
		$payload1->Delay = 0;
		
		$payload2 = new Payload();
		$payload2->Key = 'n2';
		$payload2->Delay = 0;
		
		$payload3 = new Payload();
		$payload3->Key = 'n3';
		$payload3->Delay = 0;
		
		$payloads = $this->preparePayloads([$payload1, $payload2, $payload3]);
		
		$this->getSubject()->enqueue(self::TEST_QUEUE_NAME, $payloads);
		
		$workloads = $this->getSubject()->dequeue(self::TEST_QUEUE_NAME, 0);
		
		self::assertEmpty($workloads);
		
		$workloads = $this->getSubject()->dequeue(self::TEST_QUEUE_NAME, 255);
		
		self::assertEquals(3, sizeof($workloads));
	}
	
	public function test_DequeueAll_CountBelowZero_GetEmptyArray_OtherPayloadsStayInPlace()
	{
		$payload1 = new Payload();
		$payload1->Key = 'n1';
		$payload1->Delay = 0;
		
		$payload2 = new Payload();
		$payload2->Key = 'n2';
		$payload2->Delay = 0;
		
		$payload3 = new Payload();
		$payload3->Key = 'n3';
		$payload3->Delay = 0;
		
		$payloads = $this->preparePayloads([$payload1, $payload2, $payload3]);
		
		$this->getSubject()->enqueue(self::TEST_QUEUE_NAME, $payloads);
		
		$workloads = $this->getSubject()->dequeue(self::TEST_QUEUE_NAME, -1);
		
		self::assertEmpty($workloads);
		
		$workloads = $this->getSubject()->dequeue(self::TEST_QUEUE_NAME, 255);
		
		self::assertEquals(3, sizeof($workloads));
	}
	
	public function test_DequeueAllWithBuffer_PayloadsExists_GetOnlyBufferOverflowedArray()
	{
		$payload1 = new Payload();
		$payload1->Key = 'n1';
		$payload1->Delay = 1;
		
		$payload2 = new Payload();
		$payload2->Key = 'n2';
		$payload2->Delay = 2;
		
		$payload3 = new Payload();
		$payload3->Key = 'n3';
		$payload3->Delay = 2;
		
		$payloads = $this->preparePayloads([$payload1, $payload2, $payload3]);
		
		$this->getSubject()->enqueue(self::TEST_QUEUE_NAME, $payloads);
		
		sleep(2);
		
		$workloads = $this->getSubject()->dequeue(self::TEST_QUEUE_NAME, 3, 1);
		
		self::assertEquals(3, sizeof($workloads));
		
		$this->getSubject()->flushDelayed(self::TEST_QUEUE_NAME);
		$workloads = $this->getSubject()->dequeue(self::TEST_QUEUE_NAME, 255);
		
		self::assertEquals(0, sizeof($workloads));
	}
	
	public function test_DequeueAllWithBufferAndPackageSize_DelayedExists_PackagesMoreThanSizeAllMovedToNow()
	{
		$payload1 = new Payload();
		$payload1->Key = 'd1';
		$payload1->Payload = 'payload1';
		$payload1->Delay = 1;
		
		$payload2 = new Payload();
		$payload2->Key = 'd2';
		$payload2->Delay = 1;
		
		$payload3 = new Payload();
		$payload3->Key = 'd3';
		$payload3->Delay = 1;
		
		$payload4 = new Payload();
		$payload4->Key = 'd4';
		$payload4->Delay = 3;
		
		$payloads = $this->preparePayloads([$payload1, $payload2, $payload3, $payload4]);
		
		$this->getSubject()->enqueue(self::TEST_QUEUE_NAME, $payloads);
		
		sleep(2);
		
		$workloads = $this->getSubject()
			->dequeue(self::TEST_QUEUE_NAME, 3, 6, 3);
		
		self::assertEquals(3, sizeof($workloads));
		
		$this->getSubject()->flushDelayed(self::TEST_QUEUE_NAME);
		$workloads = $this->getSubject()->dequeue(self::TEST_QUEUE_NAME, 255);
		
		self::assertEquals(1, sizeof($workloads));
	}
	
	public function test_ClearQueue_EmptyQueue_StillEmpty()
	{
		$this->getSubject()->clearQueue(self::TEST_QUEUE_NAME);
		
		self::assertEquals(0, $this->getSubject()->countEnqueued(self::TEST_QUEUE_NAME));
	}
	
	public function test_ClearQueue_NotEmptyQueue_NowEmpty()
	{
		$payload1 = new Payload();
		$payload1->Key = 'n1';
		$payload1->Delay = 0;
		
		$payload2 = new Payload();
		$payload2->Key = 'n2';
		$payload2->Delay = 5;
		
		$payloads = $this->preparePayloads([$payload1, $payload2]);
		
		$this->getSubject()->enqueue(self::TEST_QUEUE_NAME, $payloads);
		
		$this->getSubject()->clearQueue(self::TEST_QUEUE_NAME);
		
		self::assertEmpty($this->getEnqueuedIds());
		self::assertEmpty($this->getPayloads());
	}
	
		public function test_countNotDelayed_NothingExists_GotZero()
	{
		self::assertEquals(0, $this->getSubject()->countNotDelayed(self::TEST_QUEUE_NAME));
	}
	
	public function test_countNotDelayed_OnlyDelayedExists_GotZero()
	{
		$payload = new Payload();
		$payload->Key = 'n2';
		$payload->Delay = 5;
		
		$payloads = $this->preparePayloads([$payload]);
		$this->getSubject()->enqueue(self::TEST_QUEUE_NAME, $payloads);
		
		self::assertEquals(0, $this->getSubject()->countNotDelayed(self::TEST_QUEUE_NAME));
	}
	
	public function test_countNotDelayed_NowExists_GotCountOfNotDelayed()
	{
		$payload = new Payload();
		$payload->Key = 'n2';
		$payload->Delay = 5;
		
		$payload1 = new Payload();
		$payload1->Key = 'n1';
		$payload1->Delay = 0;
		
		$payloads = $this->preparePayloads([$payload, $payload1]);
		$this->getSubject()->enqueue(self::TEST_QUEUE_NAME, $payloads);
		
		self::assertEquals(1, $this->getSubject()->countNotDelayed(self::TEST_QUEUE_NAME));
	}
	
	public function test_countDelayedReadyToDequeue_NothingReady_GotZero()
	{
		self::assertEquals(0, $this->getSubject()
			->countDelayedReadyToDequeue(self::TEST_QUEUE_NAME));
	}
	
	public function test_countDelayedReadyToDequeue_ReadyExists_GotCountOfReady()
	{
		$payload = new Payload();
		$payload->Key = 'n2';
		$payload->Delay = 0.5;
		
		$payloads = $this->preparePayloads([$payload]);
		$this->getSubject()->enqueue(self::TEST_QUEUE_NAME, $payloads);
		
		sleep(1);
		
		self::assertEquals(1, 
			$this->getSubject()->countDelayedReadyToDequeue(self::TEST_QUEUE_NAME));
	}
	
	public function test_getElementByIndex_ElementNotExist_GotEmptyArray()
	{
		self::assertEmpty($this->getSubject()
			->getDelayedElementByIndex(self::TEST_QUEUE_NAME, 255));
	}
	
	public function test_getElementByIndex_ElementExist_GotElementIdAsKeyDelayAsValue()
	{
		$payload = new Payload();
		$payload->Key = 'n1';
		$payload->Delay = 0.5;
		
		$payload1 = new Payload();
		$payload1->Key = 'n2';
		$payload1->Delay = 1;
		
		$payloads = $this->preparePayloads([$payload, $payload1]);
		$this->getSubject()->enqueue(self::TEST_QUEUE_NAME, $payloads);
		
		$element = $this->getSubject()->getDelayedElementByIndex(self::TEST_QUEUE_NAME, 1);
		
		self::assertNotEmpty($element);
		self::assertEquals($payload1->Key, array_keys($element)[0]);
		self::assertGreaterThanOrEqual((time()+1) * 1000, array_values($element)[0]);
	}
	
	public function test_flushDelayed()
	{
		$payload = new Payload();
		$payload->Key = 'n1';
		$payload->Delay = 0;
		
		$payload1 = new Payload();
		$payload1->Key = 'n2';
		$payload1->Delay = 1;
		
		$payload2 = new Payload();
		$payload2->Key = 'n3';
		$payload2->Delay = 2;
		
		$payloads = $this->preparePayloads([$payload, $payload1, $payload2]);
		$this->getSubject()->enqueue(self::TEST_QUEUE_NAME, $payloads);
		
		$this->getSubject()->flushDelayed(self::TEST_QUEUE_NAME);
		
		self::assertEquals(0, $this->getSubject()->countDelayed(self::TEST_QUEUE_NAME));
		self::assertEquals(3, $this->getSubject()->countEnqueued(self::TEST_QUEUE_NAME));
	}
	
	public function test_GetFirstDelayed_NoDelayed_ReturnEmptyArray()
	{
		$firstDelayed = $this->getSubject()->getFirstDelayed(self::TEST_QUEUE_NAME);
		
		self::assertEmpty($firstDelayed);
	}
	
	public function test_GetFirstDelayed_DelayedExist_ReturnFirst()
	{
		$payload1 = new Payload();
		$payload1->Key = 'd1';
		$payload1->Payload = 'payload1';
		$payload1->Delay = 1;
		
		$payload2 = new Payload();
		$payload2->Key = 'd2';
		$payload2->Delay = 2;
		
		$payloads = $this->preparePayloads([$payload1, $payload2]);
		
		$this->getSubject()->enqueue(self::TEST_QUEUE_NAME, $payloads);
		
		$firstDelayed = $this->getSubject()->getFirstDelayed(self::TEST_QUEUE_NAME);
		
		self::assertNotEmpty($firstDelayed);
		self::assertEquals($payload1->Key, array_keys($firstDelayed)[0]);
	}
}