<?php
namespace DeepQueue\PreparedConfiguration;


use DeepQueue\DeepQueue;
use DeepQueue\Plugins\Connectors\MySQLConnector\Base\IMySQLConnector;
use DeepQueue\Plugins\Managers\RedisManager\Base\IRedisManager;
use DeepQueue\Plugins\Managers\MySQLManager\Base\IMySQLManager;
use DeepQueue\Plugins\Managers\CachedManager\Base\ICachedManager;
use DeepQueue\Plugins\Managers\InMemoryManager\Base\IInMemoryManager;
use DeepQueue\Plugins\Connectors\RedisConnector\Base\IRedisConnector;
use DeepQueue\Plugins\Connectors\FallbackConnector\Base\IFallbackConnector;
use DeepQueue\Plugins\Connectors\InMemoryConnector\Base\IInMemoryConnector;
use DeepQueue\PreparedConfiguration\Plugins\InMemoryConfiguration;

use Serialization\Serializers\JsonSerializer;
use Serialization\Json\Serializers\LiteObjectSerializer;

use PHPUnit\Framework\TestCase;


class PreparedQueueTest extends TestCase
{
	public function test_setup_passConfig_GetDeepQueue()
	{
		$config = new InMemoryConfiguration();
		self::assertInstanceOf(DeepQueue::class, PreparedQueue::setup($config));
	}
	
	public function test_InMemory_GetDeepQueue_WithInMemorySetup()
	{
		$serializer = (new JsonSerializer())->add(new LiteObjectSerializer());
		
		$dq = PreparedQueue::InMemory($serializer);
		
		self::assertInstanceOf(DeepQueue::class, $dq);
		self::assertInstanceOf(IInMemoryManager::class, $dq->config()->manager());
		self::assertInstanceOf(IInMemoryConnector::class, $dq->config()->connector());
	}
	
	public function test_RedisMySQL_GetDeepQueue_RedisConnectorMySQLManager()
	{
		$dq = PreparedQueue::RedisMySQL([], []);
		
		self::assertInstanceOf(DeepQueue::class, $dq);
		self::assertInstanceOf(IMySQLManager::class, $dq->config()->manager());
		self::assertInstanceOf(IRedisConnector::class, $dq->config()->connector());
	}
	
	public function test_FallbackCached_GetDeepQueue_FallbackConnectorCachedManager()
	{
		$dq = PreparedQueue::FallbackCached([], []);
		
		self::assertInstanceOf(DeepQueue::class, $dq);
		self::assertInstanceOf(ICachedManager::class, $dq->config()->manager());
		self::assertInstanceOf(IFallbackConnector::class, $dq->config()->connector());
	}
	
	public function test_FallbackMySQL_GetDeepQueue_FallbackConnectorMySQLManager()
	{
		$dq = PreparedQueue::FallbackMySQL([], []);
		
		self::assertInstanceOf(DeepQueue::class, $dq);
		self::assertInstanceOf(IMySQLManager::class, $dq->config()->manager());
		self::assertInstanceOf(IFallbackConnector::class, $dq->config()->connector());
	}
	
	public function test_MySQL_GetDeepQueue_MySQLConnectorMySQLManager()
	{
		$dq = PreparedQueue::MySQL([]);
		
		self::assertInstanceOf(DeepQueue::class, $dq);
		self::assertInstanceOf(IMySQLManager::class, $dq->config()->manager());
		self::assertInstanceOf(IMySQLConnector::class, $dq->config()->connector());
	}
	
	public function test_Redis_GetDeepQueue_RedisConnectorRedisManager()
	{
		$dq = PreparedQueue::Redis([]);
		
		self::assertInstanceOf(DeepQueue::class, $dq);
		self::assertInstanceOf(IRedisManager::class, $dq->config()->manager());
		self::assertInstanceOf(IRedisConnector::class, $dq->config()->connector());
	}
}