<?php
namespace DeepQueue\Plugins\Connectors\RedisConnector\Queue;


use DeepQueue\Manager\QueueConfig;
use DeepQueue\Payload;
use DeepQueue\Base\Config\IRedisConfig;
use DeepQueue\Plugins\Logger\Base\ILogger;
use DeepQueue\Plugins\Connectors\RedisConnector\Base\IRedisQueueDAO;
use DeepQueue\Plugins\Connectors\RedisConnector\DAO\RedisQueueDAO;
use DeepQueue\Utils\RedisConfigParser;

use PHPUnit\Framework\TestCase;

use Predis\Client;

use Serialization\Json\Serializers\ArraySerializer;
use Serialization\Json\Serializers\PrimitiveSerializer;
use Serialization\Serializers\JsonSerializer;


class RedisQueueTest extends TestCase
{
	private const QUEUE_NAME = 'testqueueobj';
	
	
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
	
	/**
	 * @return \PHPUnit_Framework_MockObject_MockObject|ILogger
	 */
	private function getLoggerMock(): ILogger
	{
		$logger = $this->createMock(ILogger::class);
		return $logger;
	}
	
	private function getClient(): Client
	{
		return new Client($this->getConfig()->getParameters(), $this->getConfig()->getOptions());
	}
	
	private function getSubject(): RedisQueue
	{
		return new RedisQueue(self::QUEUE_NAME, $this->getDAO(), 
			(new JsonSerializer())->add(new PrimitiveSerializer())->add(new ArraySerializer()),
			$this->getLoggerMock());
	}
	
	
	public function setUp()
	{
		$this->getClient()->eval("return redis.call('del', 'defaultKey', unpack(redis.call('keys', ARGV[1])))", 
			0, 'test.deepqueue:*');
	}
	
	public function test_enqueue_emptyPayloads_GetEmptyArray()
	{
		$ids = $this->getSubject()->enqueue([]);
		
		self::assertEmpty($ids);
	}
	
	public function test_enqueue_NotEmptyPayloads_GetIdsArray()
	{
		$payload1 = new Payload();
		$payload1->Key = 'n1';
		
		$payload2 = new Payload();
		$payload2->Key = 'n2';
		
		$ids = $this->getSubject()->enqueue([$payload1, $payload2]);

		self::assertEquals(2, sizeof($ids));
		self::assertEquals($payload1->Key, $ids[0]);
	}
	
	public function test_dequeue_countZero_getEmptyArray()
	{
		$dequeued = $this->getSubject()->dequeueWorkload(0, new QueueConfig());
		
		self::assertEmpty($dequeued);
	}
	
	public function test_dequeue_payloadsNotExist_GetEmptyArray()
	{
		$dequeued = $this->getSubject()->dequeueWorkload(255, new QueueConfig());
		
		self::assertEmpty($dequeued);
	}
	
	public function test_dequeue_payloadsExists_GetPayloadsArray()
	{
		$payload1 = new Payload();
		$payload1->Key = 'n1';
		
		$payload2 = new Payload();
		$payload2->Key = 'n2';
		
		$this->getSubject()->enqueue([$payload1, $payload2]);
		
		$dequeued = $this->getSubject()->dequeueWorkload(255, new QueueConfig());
		
		self::assertEquals(2, sizeof($dequeued));
		self::assertEquals($payload2->Key, $dequeued[1]->Id);
	}
}