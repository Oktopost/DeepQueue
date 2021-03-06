<?php
namespace DeepQueue\Plugins\Connectors\RedisConnector;


use DeepQueue\DeepQueue;
use DeepQueue\Base\Plugins\IConnectorPlugin;
use DeepQueue\Base\Plugins\ConnectorElements\IQueueManager;
use DeepQueue\Base\Queue\Remote\IRemoteQueue;
use DeepQueue\Enums\QueueLoaderPolicy;
use DeepQueue\Plugins\Managers\InMemoryManager\InMemoryManager;

use PHPUnit\Framework\TestCase;

use Serialization\Serializers\JsonSerializer;
use Serialization\Json\Serializers\ArraySerializer;
use Serialization\Json\Serializers\PrimitiveSerializer;


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
	
	
	public function test_manager_returnManager()
	{
		$manager = $this->getSubject();
		
		self::assertInstanceOf(IQueueManager::class, $manager->manager('conntest'));
	}
	
	public function test_getQueue_returnIRemoteQueue()
	{
		$manager = $this->getSubject();
		
		self::assertInstanceOf(IRemoteQueue::class, $manager->getQueue('conntest'));
	}
}