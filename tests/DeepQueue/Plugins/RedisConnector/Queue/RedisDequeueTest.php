<?php
namespace DeepQueue\Plugins\RedisConnector\Queue;


use DeepQueue\Payload;
use DeepQueue\Plugins\RedisConnector\Helper\RedisNameBuilder;
use DeepQueue\Utils\PayloadConverter;
use DeepQueue\Utils\RedisConfigParser;
use DeepQueue\Base\Config\IRedisConfig;
use DeepQueue\Plugins\RedisConnector\DAO\RedisQueueDAO;
use DeepQueue\Plugins\RedisConnector\Base\IRedisDequeue;
use DeepQueue\Plugins\RedisConnector\Base\IRedisQueueDAO;

use PHPUnit\Framework\TestCase;

use Predis\Client;

use Serialization\Serializers\JsonSerializer;
use Serialization\Json\Serializers\ArraySerializer;
use Serialization\Json\Serializers\PrimitiveSerializer;


class RedisDequeueTest extends TestCase
{
	private const QUEUE_NAME = 'testdequeue';
	
	
	private function getConfig(): IRedisConfig
	{
		$config = [];
		$config['prefix'] = 'test.deepqueue';
		
		return RedisConfigParser::parse($config);
	}
	
	private function getDAO():IRedisQueueDAO
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
	
	private function getSubject(): IRedisDequeue
	{
		return new RedisDequeue($this->getDAO(), self::QUEUE_NAME);
	}
	
	
	public function setUp()
	{
		$this->getClient()->eval("return redis.call('del', 'defaultKey', unpack(redis.call('keys', ARGV[1])))", 
			0, 'test.deepqueue:*');
	}
	
	
	public function test_dequeue_Empty_WithoutWaiting_GetEmptyArray()
	{
		self::assertEmpty($this->getSubject()->dequeue(255, 0));
	}
	
	public function test_dequeue_Empty_WithWaiting_GetEmptyArray()
	{
		self::assertEmpty($this->getSubject()->dequeue(255, 2));
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
		
		$payloads = $this->preparePayloads([$payload1, $payload2, $payload3, $payload4]);
		$this->getDAO()->enqueue(self::QUEUE_NAME, $payloads);
		
		$payloads = $this->getSubject()->dequeue(3, 0);
		
		self::assertEquals(3, sizeof($payloads));
		self::assertEquals($payload1->Key, array_keys($payloads)[0]);
		
		$leftIds = $this->getClient()
			->lrange(RedisNameBuilder::getNowKey(self::QUEUE_NAME), 0, 255);
		
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
		
		$payloads = $this->preparePayloads([$payload1, $payload2, $payload3, $payload4]);
		$this->getDAO()->enqueue(self::QUEUE_NAME, $payloads);
		
		$payloads = $this->getSubject()->dequeue(3, 3);
		
		self::assertEquals(3, sizeof($payloads));
		self::assertEquals($payload1->Key, array_keys($payloads)[0]);
		
		$leftIds = $this->getClient()
			->lrange(RedisNameBuilder::getNowKey(self::QUEUE_NAME), 0, 255);
		
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
		
		
		$payloads = $this->preparePayloads([$payload1, $payload2, $payload3, $payload4]);
		$this->getDAO()->enqueue(self::QUEUE_NAME, $payloads);
		
		$payloads = $this->getSubject()->dequeue(3, 3);
		
		self::assertEquals(1, sizeof($payloads));
		self::assertEquals($payload1->Key, array_keys($payloads)[0]);
		
		$leftIds = $this->getClient()
			->lrange(RedisNameBuilder::getNowKey(self::QUEUE_NAME), 0, 255);
		
		self::assertEmpty($leftIds);
		
		$delayIds = $this->getClient()
			->zrange(RedisNameBuilder::getDelayedKey(self::QUEUE_NAME), 0, 9999999999999999);
		
		self::assertEquals(3, sizeof($delayIds));
		self::assertEquals($payload2->Key, $delayIds[0]);
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
		
		
		$payloads = $this->preparePayloads([$payload1, $payload2]);
		$this->getDAO()->enqueue(self::QUEUE_NAME, $payloads);
		
		$payloads = $this->getSubject()->dequeue(3, 0);
		
		self::assertEmpty($payloads);
				
		$leftIds = $this->getClient()
			->lrange(RedisNameBuilder::getNowKey(self::QUEUE_NAME), 0, 255);
		
		self::assertEmpty($leftIds);
		
		$delayIds = $this->getClient()
			->zrange(RedisNameBuilder::getDelayedKey(self::QUEUE_NAME), 0, 9999999999999999);
		
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
		
		
		$payloads = $this->preparePayloads([$payload1, $payload2]);
		$this->getDAO()->enqueue(self::QUEUE_NAME, $payloads);
		
		sleep(1);
		
		$payloads = $this->getSubject()->dequeue(3, 0);
		
		self::assertEquals(1, sizeof($payloads));
				
		$leftIds = $this->getClient()
			->lrange(RedisNameBuilder::getNowKey(self::QUEUE_NAME), 0, 255);
		
		self::assertEmpty($leftIds);
		
		$delayIds = $this->getClient()
			->zrange(RedisNameBuilder::getDelayedKey(self::QUEUE_NAME), 0, 9999999999999999);
		
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
		
		$payloads = $this->preparePayloads([$payload1, $payload2, $payload3, $payload4]);
		$this->getDAO()->enqueue(self::QUEUE_NAME, $payloads);
		
		$payloads = $this->getSubject()->dequeue(4, 5);
		
		self::assertEquals(2, sizeof($payloads));
		self::assertEquals($payload1->Key, array_keys($payloads)[0]);
				
		$leftIds = $this->getClient()
			->lrange(RedisNameBuilder::getNowKey(self::QUEUE_NAME), 0, 255);
		
		self::assertEmpty($leftIds);
		
		$delayIds = $this->getClient()
			->zrange(RedisNameBuilder::getDelayedKey(self::QUEUE_NAME), 0, 9999999999999999);
		
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
		
		$payloads = $this->preparePayloads([$payload1, $payload2, $payload3, $payload4, $payload5]);
		$this->getDAO()->enqueue(self::QUEUE_NAME, $payloads);
		
		sleep(4);
		
		$payloads = $this->getSubject()->dequeue(255, 5);
		
		self::assertEquals(4, sizeof($payloads));
		self::assertEquals($payload1->Key, array_keys($payloads)[0]);
				
		$leftIds = $this->getClient()
			->lrange(RedisNameBuilder::getNowKey(self::QUEUE_NAME), 0, 255);
		
		self::assertEmpty($leftIds);
		
		$delayIds = $this->getClient()
			->zrange(RedisNameBuilder::getDelayedKey(self::QUEUE_NAME), 0, 9999999999999999);
		
		self::assertEquals(1, sizeof($delayIds));
		self::assertEquals($payload5->Key, $delayIds[0]);
	}
}