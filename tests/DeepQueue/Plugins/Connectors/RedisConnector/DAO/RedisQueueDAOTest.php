<?php
namespace DeepQueue\Plugins\Connectors\RedisConnector\DAO;


use DeepQueue\Payload;
use DeepQueue\Plugins\Connectors\RedisConnector\Helper\RedisNameBuilder;
use DeepQueue\Utils\PayloadConverter;
use DeepQueue\Utils\RedisConfigParser;
use DeepQueue\Base\Config\IRedisConfig;

use PHPUnit\Framework\TestCase;

use Predis\Client;

use Serialization\Serializers\JsonSerializer;
use Serialization\Json\Serializers\ArraySerializer;
use Serialization\Json\Serializers\PrimitiveSerializer;


class RedisQueueDAOTest extends TestCase
{
	private const QUEUE_NAME = 'ctestqueue';
	
	
	private function getConfig(): IRedisConfig
	{
		$config = [];
		$config['prefix'] = 'test.deepqueue';
		
		return RedisConfigParser::parse($config);
	}
	
	private function getSubject(): RedisQueueDAO
	{
		$config = $this->getConfig();
		
		$dao = new RedisQueueDAO();
		$dao->initClient($config);
		
		return $dao;
	}
	
	private function preparePayloads(array $payloads): array
	{
		$converter = new PayloadConverter((new JsonSerializer())
			->add(new PrimitiveSerializer())
			->add(new ArraySerializer()));
		
		return $converter->prepareAll($payloads);
	}
	
	private function getClient(): Client
	{
		return new Client($this->getConfig()->getParameters(), $this->getConfig()->getOptions());
	}
	
	
	public function setUp()
	{
		$this->getClient()->eval("return redis.call('del', 'defaultKey', unpack(redis.call('keys', ARGV[1])))", 
			0, 'test.deepqueue:*');
	}
	
	
	public function test_Enqueue_GetEmptyPayloads_ReturnEmptyArray()
	{
		$ids = $this->getSubject()->enqueue(self::QUEUE_NAME, []);
		
		self::assertEmpty($ids);
	}
	
	public function test_Enqueue_AddNowPayloads_GetPayloads()
	{
		$payload1 = new Payload();
		$payload1->Key = 'now1';
		$payload1->Payload = 'payload1';
		
		$payload2 = new Payload();
		$payload2->Key = 'now2';
		
		$payloads = $this->preparePayloads([$payload1, $payload2]);
		
		$ids = $this->getSubject()->enqueue(self::QUEUE_NAME, $payloads);
		
		self::assertEquals(2, sizeof($ids));
		self::assertEquals($payload1->Key, $ids[0]);
		
		$nowIds = $this->getClient()
			->lrange(RedisNameBuilder::getNowKey(self::QUEUE_NAME), 0, 1);
		$payloads = $this->getClient()
			->hmget(RedisNameBuilder::getPayloadsKey(self::QUEUE_NAME), [$payload1->Key, $payload2->Key]);
		
		self::assertEquals(2, sizeof($nowIds));
		self::assertEquals(2, sizeof($payloads));
		
		self::assertEquals('now1', $nowIds[0]);
		self::assertTrue(strpos($payloads[0], $payload1->Payload) !== false);
	}
	
	public function test_Enqueue_AddDelayedPayloads_GetPayloadsAndZeroKey()
	{
		$payload1 = new Payload();
		$payload1->Key = 'd1';
		$payload1->Payload = 'payload1';
		$payload1->Delay = 1;
		
		$payload2 = new Payload();
		$payload2->Key = 'd2';
		$payload2->Delay = 1;
		
		$payloads = $this->preparePayloads([$payload1, $payload2]);
		
		$this->getSubject()->enqueue(self::QUEUE_NAME, $payloads);
		
		$nowIds = $this->getClient()
			->lrange(RedisNameBuilder::getNowKey(self::QUEUE_NAME), 0, 1);
		
		$delayIds = $this->getClient()
			->zrange(RedisNameBuilder::getDelayedKey(self::QUEUE_NAME), 0, 9999999999999999);
		
		$payloads = $this->getClient()
			->hmget(RedisNameBuilder::getPayloadsKey(self::QUEUE_NAME), [$payload1->Key, $payload2->Key]);
		
		self::assertEquals(2, sizeof($delayIds));
		self::assertEquals(2, sizeof($payloads));
		
		self::assertEquals(RedisNameBuilder::getZeroKey(), $nowIds[0]);
		self::assertEquals('d1', $delayIds[0]);
		self::assertTrue(strpos($payloads[0], $payload1->Payload) !== false);
	}
	
	public function test_Enqueue_AddDelayedAndNowPayloads_GetPayloads()
	{
		$payload1 = new Payload();
		$payload1->Key = 'now1';
		$payload1->Payload = 'payload1';
		
		$payload2 = new Payload();
		$payload2->Key = 'now2';
		
		$payload3 = new Payload();
		$payload3->Key = 'd1';
		$payload3->Payload = 'payload2';
		$payload3->Delay = 1;
		
		$payload4 = new Payload();
		$payload4->Key = 'd2';
		$payload4->Delay = 1;
		
		$payloads = $this->preparePayloads([$payload1, $payload2, $payload3, $payload4]);
		
		$this->getSubject()->enqueue(self::QUEUE_NAME, $payloads);
		
		$nowIds = $this->getClient()
			->lrange(RedisNameBuilder::getNowKey(self::QUEUE_NAME), 0, 1);
		
		$delayIds = $this->getClient()
			->zrange(RedisNameBuilder::getDelayedKey(self::QUEUE_NAME), 0, 9999999999999999);
		
		$payloads = $this->getClient()
			->hmget(RedisNameBuilder::getPayloadsKey(self::QUEUE_NAME), 
				[$payload1->Key, $payload2->Key, $payload3->Key, $payload4->Key]);
		
		self::assertEquals(2, sizeof($nowIds));
		self::assertEquals(2, sizeof($delayIds));
		self::assertEquals(4, sizeof($payloads));
		
		self::assertFalse(in_array(RedisNameBuilder::getZeroKey(), $nowIds));
		
		self::assertEquals('now2', $nowIds[1]);
		self::assertEquals('d1', $delayIds[0]);
		self::assertTrue(strpos($payloads[0], $payload1->Payload) !== false);
	}
	
	public function test_DequeueInititalKey_NoKeyNoWait_ReturnNull()
	{
		$key = $this->getSubject()->dequeueInitialKey(self::QUEUE_NAME, 0);
		
		self::assertNull($key);
	}
	
	public function test_DequeueInititalKey_NoKeyWait_ReturnNull()
	{
		$key = $this->getSubject()->dequeueInitialKey(self::QUEUE_NAME, 3);
		
		self::assertNull($key);
	}
	
	public function test_DequeueInititalKey_KeyExistNoWait_ReturnString()
	{
		$payload1 = new Payload();
		$payload1->Key = 'now1';
		$payload1->Payload = 'payload1';
		
		$payloads = $this->preparePayloads([$payload1]);
		$this->getSubject()->enqueue(self::QUEUE_NAME, $payloads);
		
		$key = $this->getSubject()->dequeueInitialKey(self::QUEUE_NAME, -1);
		
		self::assertEquals($payload1->Key, $key);
	}
	
	public function test_DequeueInititalKey_KeyExistWait_ReturnString()
	{
		$payload1 = new Payload();
		$payload1->Key = 'now123';
		$payload1->Payload = 'payload1';
		
		$payloads = $this->preparePayloads([$payload1]);
		$this->getSubject()->enqueue(self::QUEUE_NAME, $payloads);
		
		$key = $this->getSubject()->dequeueInitialKey(self::QUEUE_NAME, 3);
		
		self::assertEquals($payload1->Key, $key);
	}
	
	public function test_DequeueAll_NoInitialKey_NoPayloads_ReturnEmptyArray()
	{
		$payloads = $this->getSubject()->dequeueAll(self::QUEUE_NAME, 255);
		
		self::assertEmpty($payloads);
	}
	
	public function test_DequeueAll_NoInitialKey_PayloadsExist_ReturnArray()
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
		
		$payloads = $this->preparePayloads([$payload1, $payload2, $payload3, $payload4]);
		$this->getSubject()->enqueue(self::QUEUE_NAME, $payloads);
		
		$payloads = $this->getSubject()->dequeueAll(self::QUEUE_NAME, 2);
		
		self::assertEquals(2, sizeof($payloads));
		self::assertEquals($payload1->Key, array_keys($payloads)[0]);
		
		$leftIds = $this->getClient()
			->lrange(RedisNameBuilder::getNowKey(self::QUEUE_NAME), 0, 255);
		
		self::assertEquals(2, sizeof($leftIds));
		self::assertEquals($payload3->Key, $leftIds[0]);
	}
	
	public function test_DequeueAll_InitialKeyPassed_PayloadsExist_ReturnArray()
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
		
		$payload5 = new Payload();
		$payload5->Key = 'now5';
		
		$payloads = $this->preparePayloads([$payload1, $payload2, $payload3, $payload4, $payload5]);
		$this->getSubject()->enqueue(self::QUEUE_NAME, $payloads);
		
		$key = $this->getSubject()->dequeueInitialKey(self::QUEUE_NAME, -1);
		
		self::assertNotNull($key);
		
		$payloads = $this->getSubject()->dequeueAll(self::QUEUE_NAME, 2, $key);
		
		self::assertEquals(3, sizeof($payloads));
		self::assertEquals($payload1->Key, array_keys($payloads)[0]);
		
		$leftIds = $this->getClient()
			->lrange(RedisNameBuilder::getNowKey(self::QUEUE_NAME), 0, 255);
		
		self::assertEquals(2, sizeof($leftIds));
		self::assertEquals($payload4->Key, $leftIds[0]);
	}
	
	public function test_PopDelayed_NoDelayed()
	{
		$this->getSubject()->popDelayed(self::QUEUE_NAME);
		
		$now = $this->getClient()
			->lrange(RedisNameBuilder::getNowKey(self::QUEUE_NAME), 0, 255);
		
		$delayIds = $this->getClient()
			->zrange(RedisNameBuilder::getDelayedKey(self::QUEUE_NAME), 0, 9999999999999999);
		
		self::assertEmpty($now);
		self::assertEmpty($delayIds);
	}
	
	public function test_PopDelayed_DelayedExists_DelayedMovedToNow()
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
		$payload4->Delay = 5;
		
		$payloads = $this->preparePayloads([$payload1, $payload2, $payload3, $payload4]);
		
		$this->getSubject()->enqueue(self::QUEUE_NAME, $payloads);
		
		sleep(1);
		
		$this->getSubject()->popDelayed(self::QUEUE_NAME);
		
		$now = $this->getClient()
			->lrange(RedisNameBuilder::getNowKey(self::QUEUE_NAME), 0, 255);
		
		$delayIds = $this->getClient()
			->zrange(RedisNameBuilder::getDelayedKey(self::QUEUE_NAME), 0, 9999999999999999);
		
		self::assertEquals(4, sizeof($now));
		self::assertEquals($payload1->Key, $now[1]);
		self::assertTrue(in_array(RedisNameBuilder::getZeroKey(), $now));
		
		self::assertEquals(1, sizeof($delayIds));
		self::assertEquals($payload4->Key, $delayIds[0]);
	}
	
	public function test_PopDelayedWithBuffer_DelayedExists_BufferOverflowedAndDelayedMovedToNow()
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
		$payload3->Delay = 2;
		
		$payload4 = new Payload();
		$payload4->Key = 'd4';
		$payload4->Delay = 3;
		
		$payloads = $this->preparePayloads([$payload1, $payload2, $payload3, $payload4]);
		
		$this->getSubject()->enqueue(self::QUEUE_NAME, $payloads);
		
		sleep(2);
		
		$this->getSubject()->popDelayed(self::QUEUE_NAME, 1);
		
		$now = $this->getClient()
			->lrange(RedisNameBuilder::getNowKey(self::QUEUE_NAME), 0, 255);
		
		$delayIds = $this->getClient()
			->zrange(RedisNameBuilder::getDelayedKey(self::QUEUE_NAME), 0, 9999999999999999);
		
		self::assertEquals(4, sizeof($now));
		self::assertEquals($payload1->Key, $now[1]);
		self::assertTrue(in_array(RedisNameBuilder::getZeroKey(), $now));
		
		self::assertEquals(1, sizeof($delayIds));
		self::assertEquals($payload4->Key, $delayIds[0]);
	}
	
	public function test_PopDelayedWithBufferAndPackageSize_DelayedExists_PackagesMoreThanSizeAllMovedToNow()
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
		
		$this->getSubject()->enqueue(self::QUEUE_NAME, $payloads);
		
		sleep(2);
		
		$this->getSubject()->popDelayed(self::QUEUE_NAME, 5, 3);
		
		$now = $this->getClient()
			->lrange(RedisNameBuilder::getNowKey(self::QUEUE_NAME), 0, 255);
		
		$delayIds = $this->getClient()
			->zrange(RedisNameBuilder::getDelayedKey(self::QUEUE_NAME), 0, 9999999999999999);
		
		self::assertEquals(4, sizeof($now));
		self::assertEquals($payload1->Key, $now[1]);
		self::assertTrue(in_array(RedisNameBuilder::getZeroKey(), $now));
		
		self::assertEquals(1, sizeof($delayIds));
		self::assertEquals($payload4->Key, $delayIds[0]);
	}
	
	public function test_GetFirstDelayed_NoDelayed_ReturnEmptyArray()
	{
		$firstDelayed = $this->getSubject()->getFirstDelayed(self::QUEUE_NAME);
		
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
		
		$this->getSubject()->enqueue(self::QUEUE_NAME, $payloads);
		
		$firstDelayed = $this->getSubject()->getFirstDelayed(self::QUEUE_NAME);
		
		self::assertNotEmpty($firstDelayed);
		self::assertEquals($payload1->Key, array_keys($firstDelayed)[0]);
	}
	
	public function test_countEnqueued_NoEnqueued_ReturnZero()
	{
		self::assertEquals(0, $this->getSubject()->countEnqueued(self::QUEUE_NAME));
	}
	
	public function test_countEnqueued_EnqueuedExist_ReturnAmount()
	{
		$payload1 = new Payload();
		$payload1->Key = 'd1';
		$payload1->Payload = 'payload1';
		$payload1->Delay = 1;
		
		$payload2 = new Payload();
		$payload2->Key = 'n1';
		$payload2->Delay = 0;
		
		$payloads = $this->preparePayloads([$payload1, $payload2]);
		
		$this->getSubject()->enqueue(self::QUEUE_NAME, $payloads);
		
		self::assertEquals(2, $this->getSubject()->countEnqueued(self::QUEUE_NAME));
	}
	
	public function test_countDelayed_NoDelayed_ReturnZero()
	{
		self::assertEquals(0, $this->getSubject()->countDelayed(self::QUEUE_NAME));
	}
	
	public function test_countDelayed_DelayedExist_ReturnAmount()
	{
		$payload1 = new Payload();
		$payload1->Key = 'd1';
		$payload1->Payload = 'payload1';
		$payload1->Delay = 1;
		
		$payload2 = new Payload();
		$payload2->Key = 'n1';
		$payload2->Delay = 0;
		
		$payloads = $this->preparePayloads([$payload1, $payload2]);
		
		$this->getSubject()->enqueue(self::QUEUE_NAME, $payloads);
		
		self::assertEquals(1, $this->getSubject()->countDelayed(self::QUEUE_NAME));
	}
	
	public function test_NotUpdateExistingDelayedTime_ReturnPayload()
	{
		$payload1 = new Payload();
		$payload1->Key = 'd1';
		$payload1->Payload = 'payload1';
		$payload1->Delay = 2;
		
		$payloads = $this->preparePayloads([$payload1]);
		
		$this->getSubject()->enqueue(self::QUEUE_NAME, $payloads);
		
		sleep(3);
		
		$this->getSubject()->enqueue(self::QUEUE_NAME, $payloads);
		
		$this->getSubject()->popDelayed(self::QUEUE_NAME);
		
		//count = 2 because first one is zerokey
		$payloads = $this->getSubject()->dequeueAll(self::QUEUE_NAME, 2);
		
		self::assertNotEmpty($payloads);
		self::assertEquals($payload1->Key, array_keys($payloads)[0]);
	}
	
	public function test_DequeueAll_GetOnePayloadManyEnqueued_GetOneOtherExistsAfter()
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
		
		$this->getSubject()->enqueue(self::QUEUE_NAME, $payloads);
		
		$workload = $this->getSubject()->dequeueAll(self::QUEUE_NAME, 1);
		
		self::assertEquals(1, sizeof($workload));
		self::assertEquals($payload1->Key, array_keys($workload)[0]);
		
		$workloads = $this->getSubject()->dequeueAll(self::QUEUE_NAME, 2);
		
		self::assertEquals(2, sizeof($workloads));
		
		self::assertEquals($payload2->Key, array_keys($workloads)[0]);
		self::assertEquals($payload3->Key, array_keys($workloads)[1]);
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
		
		$this->getSubject()->enqueue(self::QUEUE_NAME, $payloads);
		
		$workloads = $this->getSubject()->dequeueAll(self::QUEUE_NAME, 0);
		
		self::assertEmpty($workloads);
		
		$workloads = $this->getSubject()->dequeueAll(self::QUEUE_NAME, 255);
		
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
		
		$this->getSubject()->enqueue(self::QUEUE_NAME, $payloads);
		
		$workloads = $this->getSubject()->dequeueAll(self::QUEUE_NAME, -1);
		
		self::assertEmpty($workloads);
		
		$workloads = $this->getSubject()->dequeueAll(self::QUEUE_NAME, 255);
		
		self::assertEquals(3, sizeof($workloads));
	}
	
	public function test_ClearQueue_EmptyQueue_StillEmpty()
	{
		$this->getSubject()->clearQueue(self::QUEUE_NAME);
		
		self::assertEquals(0, $this->getSubject()->countEnqueued(self::QUEUE_NAME));
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
		
		$this->getSubject()->enqueue(self::QUEUE_NAME, $payloads);
		
		$this->getSubject()->clearQueue(self::QUEUE_NAME);
		
		$now = $this->getClient()
			->lrange(RedisNameBuilder::getNowKey(self::QUEUE_NAME), 0, 255);
		
		$delayIds = $this->getClient()
			->zrange(RedisNameBuilder::getDelayedKey(self::QUEUE_NAME), 0, 9999999999999999);
		
		$payloads = $this->getClient()
			->hgetall(RedisNameBuilder::getPayloadsKey(self::QUEUE_NAME));
		
		self::assertEmpty($now);
		self::assertEmpty($delayIds);
		self::assertEmpty($payloads);
	}
	
	public function test_countNotDelayed_NothingExists_GotZero()
	{
		self::assertEquals(0, $this->getSubject()->countNotDelayed(self::QUEUE_NAME));
	}
	
	public function test_countNotDelayed_OnlyDelayedExists_GotZero()
	{
		$payload = new Payload();
		$payload->Key = 'n2';
		$payload->Delay = 5;
		
		$payloads = $this->preparePayloads([$payload]);
		$this->getSubject()->enqueue(self::QUEUE_NAME, $payloads);
		
		self::assertEquals(0, $this->getSubject()->countNotDelayed(self::QUEUE_NAME));
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
		$this->getSubject()->enqueue(self::QUEUE_NAME, $payloads);
		
		self::assertEquals(1, $this->getSubject()->countNotDelayed(self::QUEUE_NAME));
	}
	
	public function test_countDelayedReadyToDequeue_NothingReady_GotZero()
	{
		self::assertEquals(0, $this->getSubject()
			->countDelayedReadyToDequeue(self::QUEUE_NAME));
	}
	
	public function test_countDelayedReadyToDequeue_ReadyExists_GotCountOfReady()
	{
		$payload = new Payload();
		$payload->Key = 'n2';
		$payload->Delay = 0.5;
		
		$payloads = $this->preparePayloads([$payload]);
		$this->getSubject()->enqueue(self::QUEUE_NAME, $payloads);
		
		sleep(1);
		
		self::assertEquals(1, 
			$this->getSubject()->countDelayedReadyToDequeue(self::QUEUE_NAME));
	}
	
	public function test_getElementByIndex_ElementNotExist_GotEmptyArray()
	{
		self::assertEmpty($this->getSubject()
			->getDelayedElementByIndex(self::QUEUE_NAME, 255));
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
		$this->getSubject()->enqueue(self::QUEUE_NAME, $payloads);
		
		$element = $this->getSubject()->getDelayedElementByIndex(self::QUEUE_NAME, 1);
		
		self::assertNotEmpty($element);
		self::assertEquals($payload1->Key, array_keys($element)[0]);
		self::assertGreaterThan((time()+1) * 1000, array_values($element)[0]);
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
		$this->getSubject()->enqueue(self::QUEUE_NAME, $payloads);
		
		$this->getSubject()->flushDelayed(self::QUEUE_NAME);
		
		self::assertEquals(0, $this->getSubject()->countDelayed(self::QUEUE_NAME));
		self::assertEquals(3, $this->getSubject()->countEnqueued(self::QUEUE_NAME));
	}
}