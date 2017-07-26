<?php
namespace DeepQueue\Plugins\Connectors\VoidConnector;


use DeepQueue\DeepQueueConfig;
use DeepQueue\Base\Queue\Remote\IRemoteQueue;
use DeepQueue\Base\Plugins\ConnectorElements\IQueueManager;

use PHPUnit\Framework\TestCase;


class VoidConnectorTest extends TestCase
{
	private function getSubject(): VoidConnector
	{
		$void = new VoidConnector();
		$void->setDeepConfig(new DeepQueueConfig());
		
		return $void;
	}
	
	
	public function test_manager_returnManager()
	{
		$manager = $this->getSubject();
		
		self::assertInstanceOf(IQueueManager::class, $manager->manager('conntest'));
	}
	
	public function test_getQueue()
	{
		$queue = $this->getSubject()->getQueue('test.queue');
		
		self::assertInstanceOf(IRemoteQueue::class, $queue);
	}
}