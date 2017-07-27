<?php
namespace DeepQueue\Plugins\Connectors\MySQLConnector\Manager;


use DeepQueue\Base\IMetaData;
use DeepQueue\Plugins\Connectors\InMemoryConnector\Base\IInMemoryQueueDAO;
use DeepQueue\Plugins\Connectors\InMemoryConnector\Base\IInMemoryQueueManager;
use DeepQueue\Plugins\Connectors\InMemoryConnector\Manager\InMemoryQueueManager;

use PHPUnit\Framework\TestCase;


class InMemoryQueueManagerTest extends TestCase
{
	private const QUEUE_NAME = 'testqueueobj';
	
	/**
	 * @return \PHPUnit_Framework_MockObject_MockObject|IInMemoryQueueDAO
	 */
	private function getDAOMock(): IInMemoryQueueDAO
	{
		$memoryDAO = $this->createMock(IInMemoryQueueDAO::class);
		$memoryDAO->method('countEnqueued')->willReturn(0);
		

		return $memoryDAO;
	}

	private function getSubject(?string $name = null, IInMemoryQueueDAO $dao): IInMemoryQueueManager
	{
		$manager = new InMemoryQueueManager($dao);
		$manager->flushDelayed();
		
		if ($name)
		{
			$manager->setQueueName($name);
		}
		
		return $manager;
	}
	
	
	/**
	 * @expectedException \DeepQueue\Exceptions\InvalidUsageException
	 */
	public function test_getMetaData_NotQueueNameSettedUp_ExceptionThrowed()
	{
		$this->getSubject(null, $this->getDAOMock())->getMetaData();
	}
	
	public function test_getMetaData_NameSettedup_MetaDataReturned()
	{
		self::assertInstanceOf(IMetaData::class, $this
			->getSubject(self::QUEUE_NAME, $this->getDAOMock())->getMetaData());
	}

	/**
	 * @expectedException \DeepQueue\Exceptions\InvalidUsageException
	 */
	public function test_clearQueue_NotQueueNameSpecified_ExceptionThrowed()
	{
		$this->getSubject(null, $this->getDAOMock())->clearQueue();
	}
	
	public function test_clearQueue_QueueNameSpecified_QueueClearInDAOCalled()
	{
		$dao = $this->getDAOMock();
		
		/** @var $dao \PHPUnit_Framework_MockObject_MockObject */
		$dao->expects($this->once())->method('clearQueue');
		
		$this->getSubject(self::QUEUE_NAME, $dao)->clearQueue();
	}
	
	public function test_getWaitingTime_NoElements_GotNull()
	{
		self::assertNull($this->getSubject(self::QUEUE_NAME, 
			$this->getDAOMock())->getWaitingTime());
	}
	
	public function test_getWaitingTime_ElementsExists_GotZero()
	{
		$dao = $this->getDAOMock();
		$dao->method('countEnqueued')->willReturn(1);
		
		self::assertEquals(0, $this->getSubject(self::QUEUE_NAME, 
			$this->getDAOMock())->getWaitingTime());
	}
}