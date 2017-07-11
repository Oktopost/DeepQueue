<?php
namespace DeepQueue\Plugins\Connectors\VoidConnector;


use DeepQueue\DeepQueueConfig;
use DeepQueue\Base\IMetaData;
use DeepQueue\Base\Queue\Remote\IRemoteQueue;
use DeepQueue\Manager\QueueObject;

use PHPUnit\Framework\TestCase;


class VoidConnectorTest extends TestCase
{
	private function getSubject(): VoidConnector
	{
		$void = new VoidConnector();
		$void->setDeepConfig(new DeepQueueConfig());
		
		return $void;
	}
	
	
	public function test_getMetaData()
	{
		$metaData = $this->getSubject()->getMetaData(new QueueObject());
		
		self::assertInstanceOf(IMetaData::class, $metaData);
		self::assertEquals(0, $metaData->Delayed);
		self::assertEquals(0, $metaData->Enqueued);
	}
	
	public function test_getQueue()
	{
		$queue = $this->getSubject()->getQueue('test.queue');
		
		self::assertInstanceOf(IRemoteQueue::class, $queue);
	}
}