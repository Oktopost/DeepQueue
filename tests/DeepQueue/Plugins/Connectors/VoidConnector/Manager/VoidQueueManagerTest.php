<?php
namespace DeepQueue\Plugins\Connectors\VoidConnector\Manager;


use DeepQueue\Base\IMetaData;


use PHPUnit\Framework\TestCase;


class MySQLQueueManagerTest extends TestCase
{
	private function getSubject(): VoidQueueManager
	{
		return new VoidQueueManager();
	}
	
	
	public function test_sanity()
	{
		$this->getSubject()->clearQueue();
		$this->getSubject()->setQueueName('test');
		$this->getSubject()->flushDelayed();
		
		self::assertInstanceOf(IMetaData::class, 
			$this->getSubject()->getMetaData());
		
		self::assertEquals(null, $this->getSubject()->getWaitingTime());
	}
}