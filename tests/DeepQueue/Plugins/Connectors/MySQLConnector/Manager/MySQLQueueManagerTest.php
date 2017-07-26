<?php
namespace DeepQueue\Plugins\Connectors\MySQLConnector\Manager;


use DeepQueue\Base\IMetaData;
use DeepQueue\Plugins\Connectors\MySQLConnector\Base\DAO\IMySQLQueueDAO;
use DeepQueue\Plugins\Connectors\MySQLConnector\Base\IMySQLQueueManager;


use PHPUnit\Framework\TestCase;


class MySQLQueueManagerTest extends TestCase
{
	private const QUEUE_NAME = 'testqueueobj';
	
	/**
	 * @return \PHPUnit_Framework_MockObject_MockObject|IMySQLQueueDAO
	 */
	private function getDAOMock(): IMySQLQueueDAO
	{
		$redisDAO = $this->createMock(IMySQLQueueDAO::class);
		$redisDAO->method('countEnqueued')->willReturn(0);
		$redisDAO->method('countDelayed')->willReturn(0);
		
		return $redisDAO;
	}

	private function getSubject(?string $name = null, IMySQLQueueDAO $dao): IMySQLQueueManager
	{
		$manager = new MySQLQueueManager($dao);
		
		if ($name)
		{
			$manager->setQueueName($name);
		}
		
		return $manager;
	}
	
	
	public function test_getMetaData_NotQueueNameSettedUp_MetaDataReturned()
	{
		self::assertInstanceOf(IMetaData::class, 
			$this->getSubject(null, $this->getDAOMock())->getMetaData());
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
}