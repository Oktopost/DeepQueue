<?php
namespace DeepQueue\Plugins\RedisConnector;


use DeepQueue\Base\IDeepQueueConfig;
use DeepQueue\Base\IMetaData;
use DeepQueue\Base\Plugins\IConnectorPlugin;
use DeepQueue\Base\Queue\Remote\IRemoteQueue;
use DeepQueue\DeepQueue;
use DeepQueue\Enums\QueueLoaderPolicy;
use DeepQueue\Plugins\InMemoryManager\InMemoryManager;
use DeepQueue\Plugins\RedisConnector\Base\IRedisConnector;

use DeepQueue\Plugins\RedisManager\RedisManager;
use PHPUnit\Framework\TestCase;
use Serialization\Json\Serializers\ArraySerializer;
use Serialization\Json\Serializers\PrimitiveSerializer;
use Serialization\Serializers\JsonSerializer;


class RedisConnectorTest extends TestCase
{
	private function getDeepQueue(): DeepQueue
	{
		$dq = new DeepQueue();
		
		$dq->config()
			->setManagerPlugin(new InMemoryManager())
			->setQueueNotExistsPolicy(QueueLoaderPolicy::CREATE_NEW)
			->setConnectorPlugin(new RedisConnector(['prefix' => 'redis.conn.test']))
			->setSerializer((new JsonSerializer())->add(new ArraySerializer())->add(new PrimitiveSerializer()));
		
		return $dq;
	}
	
	private function getSubject(): IConnectorPlugin
	{
		return $this->getDeepQueue()->config()->connector();
	}
	
	
	public function test_getMetaDate_returnIMetaData()
	{
		$manager = $this->getSubject();
		
		self::assertInstanceOf(IMetaData::class, $manager->getMetaData($this->getDeepQueue()
			->getQueueObject('conntest')));
	}
	
	public function test_getQueue_returnIRemoteQueue()
	{
		$manager = $this->getSubject();
		
		self::assertInstanceOf(IRemoteQueue::class, $manager->getQueue('conntest'));
	}
}