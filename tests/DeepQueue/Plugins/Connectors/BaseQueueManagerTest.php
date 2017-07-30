<?php
namespace DeepQueue\Plugins\Connectors;


use DeepQueue\Base\Plugins\ConnectorElements\IQueueManagerDAO;
use DeepQueue\Utils\TimeGenerator;
use PHPUnit\Framework\TestCase;


class BaseQueueManagerTest extends TestCase
{
	private const QUEUE_NAME = 'test.queue';
	
	/**
	 * @return \PHPUnit_Framework_MockObject_MockObject|IQueueManagerDAO
	 */
	private function getDAOMock(): IQueueManagerDAO
	{
		$dao = $this->createMock(IQueueManagerDAO::class);
		$dao->method('countEnqueued')->willReturn(0);
		$dao->method('countDelayed')->willReturn(0);
		
		return $dao;
	}
	
	private function getSubject(?IQueueManagerDAO $dao = null): BaseQueueManager
	{
		$manager = new BaseQueueManager();
		$manager->setQueueName(self::QUEUE_NAME);
		
		if (!$dao)
		{
			$dao = $this->getDAOMock();
		}
		
		$manager->setDAO($dao);
		
		return $manager;
	}
	
	public function test_getWaitingTime_IsNotDelatedExists_GotZero()
	{
		$dao = $this->getDAOMock();
		$dao->method('countNotDelayed')->willReturn(1);
		
		self::assertEquals(0, $this->getSubject($dao)->getWaitingTime(1));
	}
	
	public function test_getWaitingTime_NoDelayedData_GotNull()
	{
		$dao = $this->getDAOMock();
		$dao->method('getFirstDelayed')->willReturn([]);
		
		self::assertNull($this->getSubject($dao)->getWaitingTime(1));
	}
	
	public function test_getWaitingTime_DelayedDataExist_GotOffset()
	{
		$time = TimeGenerator::getMs(1);
		
		$dao = $this->getDAOMock();
		$dao->method('getFirstDelayed')->willReturn(['a' => $time]);
		
		$offset = $this->getSubject($dao)->getWaitingTime(1);
		
		self::assertGreaterThan(1.9, $offset);
		self::assertLessThanOrEqual(2, $offset);
	}
	
	public function test_getWaitingTimeWithBulkSize_DelayedDataExist_BulkReady_GotZero()
	{
		$time = TimeGenerator::getMs(1);
		
		$dao = $this->getDAOMock();
		$dao->method('getFirstDelayed')->willReturn(['a' => $time]);
		$dao->method('countDelayedReadyToDequeue')->willReturn(1);

		$offset = $this->getSubject($dao)->getWaitingTime(2, 1);
		
		self::assertEquals(0, $offset);
	}
	
	public function test_getWaitingTimeWithBulkSize_DelayedDataExist_BufferOverflowFirst_GotBufferDelay()
	{
		$bufferOverflowTime = TimeGenerator::getMs(1);
		$bulkReadyTime = TimeGenerator::getMs(2);
		
		$dao = $this->getDAOMock();
		$dao->method('getFirstDelayed')->willReturn(['a' => $bufferOverflowTime]);
		$dao->method('countDelayedReadyToDequeue')->willReturn(1);
		$dao->method('getDelayedElementByIndex')->willReturn(['a' => $bulkReadyTime]);


		$offset = $this->getSubject($dao)->getWaitingTime(2, 3);
		
		self::assertGreaterThan(1.9, $offset);
		self::assertLessThanOrEqual(2, $offset);
	}
	
	public function test_getWaitingTimeWithBulkSize_DelayedDataExist_BulkWillBeReadyFirst_GotBulkDelay()
	{
		$bufferOverflowTime = TimeGenerator::getMs(2);
		$bulkReadyTime = TimeGenerator::getMs(1);
		
		$dao = $this->getDAOMock();
		$dao->method('getFirstDelayed')->willReturn(['a' => $bufferOverflowTime]);
		$dao->method('countDelayedReadyToDequeue')->willReturn(2);
		$dao->method('getDelayedElementByIndex')->willReturn(['a' => $bulkReadyTime]);


		$offset = $this->getSubject($dao)->getWaitingTime(5, 3);
		
		self::assertGreaterThan(0.9, $offset);
		self::assertLessThanOrEqual(1, $offset);
	}
	
	public function test_flushDelayed()
	{
		$dao = $this->getDAOMock();
		$dao->expects($this->once())->method('flushDelayed');
		
		$this->getSubject($dao)->flushDelayed();
	}
}