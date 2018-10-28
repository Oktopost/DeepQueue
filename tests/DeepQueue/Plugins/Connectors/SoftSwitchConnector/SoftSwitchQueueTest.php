<?php
namespace DeepQueue\Plugins\Connectors\SoftSwitchConnector;


use CosmicRay\Wrappers\PHPUnit\UnitestCase;

use DeepQueue\Base\IQueueConfig;
use DeepQueue\Base\Queue\Remote\IRemoteQueue;


class SoftSwitchQueueTest extends UnitestCase
{
	/** @var IRemoteQueue|\PHPUnit_Framework_MockObject_MockObject */
	private $fromQueue;
	
	/** @var IRemoteQueue|\PHPUnit_Framework_MockObject_MockObject */
	private $toQueue;
	
	
	public function getSubject(): SoftSwitchQueue
	{
		$this->fromQueue		= $this->getMockBuilder(IRemoteQueue::class)->getMock();
		$this->toQueue			= $this->getMockBuilder(IRemoteQueue::class)->getMock();
		
		$subject = new SoftSwitchQueue($this->fromQueue, $this->toQueue);
		
		return $subject;
	}
	
	public function getQueueConfig(): IQueueConfig
	{
		/** @var IQueueConfig $config */
		$config = $this->getMockBuilder(IQueueConfig::class)->getMock();
		return $config;
	}
	
	
	public function test_enqueue_OnlyTargetQueueGetsThePayload(SoftSwitchQueue $subject): void
	{
		$this->fromQueue->expects($this->never())->method('enqueue');
		$this->toQueue->expects($this->once())->method('enqueue')->with([1, 2, 3])->willReturn(['a', 'b', 'c']);
		
		self::assertEquals(['a', 'b', 'c'], $subject->enqueue([1, 2, 3]));
	}
	
	
	public function test_dequeueWorkload_FromQueueHasData_ToQueueNotCalled(SoftSwitchQueue $subject, IQueueConfig $config): void
	{
		$this->fromQueue->expects($this->once())->method('dequeueWorkload')->willReturn([1]);
		$this->toQueue->expects($this->never())->method('dequeueWorkload');
		
		$subject->dequeueWorkload(123, $config);
	}
	
	public function test_dequeueWorkload_FromQueueMissingData_ToQueueCalled(SoftSwitchQueue $subject, IQueueConfig $config): void
	{
		$this->fromQueue->expects($this->once())->method('dequeueWorkload')->willReturn([]);
		$this->toQueue->expects($this->once())->method('dequeueWorkload');
		
		$subject->dequeueWorkload(123, $config);
	}
	
	public function test_dequeueWorkload_DataInFromQueueReturned(SoftSwitchQueue $subject, IQueueConfig $config): void
	{
		$this->fromQueue->expects($this->once())->method('dequeueWorkload')->willReturn([1, 2]);
		
		self::assertEquals([1, 2], $subject->dequeueWorkload(123, $config));
	}
	
	public function test_dequeueWorkload_DataInToQueueReturned(SoftSwitchQueue $subject, IQueueConfig $config): void
	{
		$this->fromQueue->expects($this->once())->method('dequeueWorkload')->willReturn([]);
		$this->toQueue->expects($this->once())->method('dequeueWorkload')->willReturn([2, 3]);
		
		self::assertEquals([2, 3], $subject->dequeueWorkload(123, $config));
	}
	
	public function test_dequeueWorkload_CorrectParamsPassedToQueue(SoftSwitchQueue $subject, IQueueConfig $config): void
	{
		$this->fromQueue->expects($this->once())->method('dequeueWorkload')
			->with(
				123,
				$config,
				null
			)
			->willReturn([]);
		
		$this->toQueue->expects($this->once())->method('dequeueWorkload')
			->with(
				123,
				$config,
				5.3
			)
			->willReturn([]);
		
		$subject->dequeueWorkload(123, $config, 5.3);
	}
}