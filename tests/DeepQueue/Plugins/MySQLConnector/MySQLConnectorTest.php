<?php
namespace DeepQueue\Plugins\Connectors\MySQLConnector;


use DeepQueue\DeepQueue;
use DeepQueue\Base\IMetaData;
use DeepQueue\Base\Plugins\IConnectorPlugin;
use DeepQueue\Base\Queue\Remote\IRemoteQueue;
use DeepQueue\Enums\QueueLoaderPolicy;
use DeepQueue\Plugins\Managers\InMemoryManager\InMemoryManager;

use lib\MySQLConfig;

use PHPUnit\Framework\TestCase;

use Serialization\Serializers\JsonSerializer;
use Serialization\Json\Serializers\ArraySerializer;
use Serialization\Json\Serializers\PrimitiveSerializer;


class MySQLConnectorTest extends TestCase
{
	private function getDeepQueue(): DeepQueue
	{
		$dq = new DeepQueue();
		
		$dq->config()
			->setManagerPlugin(new InMemoryManager())
			->setQueueNotExistsPolicy(QueueLoaderPolicy::CREATE_NEW)
			->setConnectorPlugin(new MySQLConnector(MySQLConfig::get()))
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