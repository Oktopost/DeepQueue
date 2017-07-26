<?php
namespace DeepQueue\Plugins\Connectors\RedisConnector\Manager;


use DeepQueue\Base\IMetaData;
use DeepQueue\Plugins\Connectors\RedisConnector\Base\IRedisQueueDAO;
use DeepQueue\Plugins\Connectors\RedisConnector\Base\IRedisQueueManager;

use PHPUnit\Framework\TestCase;


class RedisQueueManagerTest extends TestCase
{
	private const QUEUE_NAME = 'testqueueobj';

	/**
	 * @return \PHPUnit_Framework_MockObject_MockObject|IRedisQueueDAO
	 */
	private function getDAOMock(): IRedisQueueDAO
	{
		$redisDAO = $this->createMock(IRedisQueueDAO::class);
		$redisDAO->method('countEnqueued')->willReturn(0);
		$redisDAO->method('countDelayed')->willReturn(0);
		
		return $redisDAO;
	}

	private function getSubject(?string $name = null, IRedisQueueDAO $dao): IRedisQueueManager
	{
		$manager = new RedisQueueManager($dao);
		
		if ($name)
		{
			$manager->setQueueName($name);
		}
		
		return $manager;
	}
	
	
	public function test_getMetaData_NotQueueNameSettedUp_MetaDataReturned()
	{
		self::assertInstanceOf(IMetaData::class, $this->getSubject(null, $this->getDAOMock())->getMetaData());
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