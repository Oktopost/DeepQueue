<?php
namespace DeepQueue\Plugins\Connectors\SoftSwitchConnector;


use CosmicRay\Wrappers\PHPUnit\UnitestCase;

use DeepQueue\Base\IQueueConfig;
use DeepQueue\Base\IDeepQueueConfig;
use DeepQueue\Base\Queue\Remote\IRemoteQueue;
use DeepQueue\Base\Plugins\IConnectorPlugin;
use DeepQueue\Base\Plugins\ConnectorElements\IQueueManager;


class SoftSwitchConnectorTest extends UnitestCase
{
	/** @var IRemoteQueue|\PHPUnit_Framework_MockObject_MockObject */
	private $fromQueue;
	
	/** @var IRemoteQueue|\PHPUnit_Framework_MockObject_MockObject */
	private $toQueue;
	
	/** @var IConnectorPlugin|\PHPUnit_Framework_MockObject_MockObject */
	private $fromConnector;
	
	/** @var IConnectorPlugin|\PHPUnit_Framework_MockObject_MockObject */
	private $toConnector;
	
	
	public function getSubject(): SoftSwitchConnector
	{
		$this->fromQueue		= $this->getMockBuilder(IRemoteQueue::class)->getMock();
		$this->toQueue			= $this->getMockBuilder(IRemoteQueue::class)->getMock();
		$this->fromConnector	= $this->getMockBuilder(IConnectorPlugin::class)->getMock();
		$this->toConnector		= $this->getMockBuilder(IConnectorPlugin::class)->getMock();
		
		$this->fromConnector->method('getQueue')->willReturn($this->fromQueue);
		$this->toConnector->method('getQueue')->willReturn($this->toQueue);
		
		$subject = new SoftSwitchConnector($this->fromConnector, $this->toConnector);
		
		return $subject;
	}
	
	
	public function test_setDeepConfig_CalledOnChildren(SoftSwitchConnector $subject): void
	{
		/** @var IDeepQueueConfig $config */
		$config = $this->getMockBuilder(IDeepQueueConfig::class)->getMock();
		
		$this->fromConnector->expects($this->once())->method('setDeepConfig')->with($config);
		$this->toConnector->expects($this->once())->method('setDeepConfig')->with($config);
		
		$subject->setDeepConfig($config);
	}
	
	
	public function test_manager_ManagerFromTargetReturned(SoftSwitchConnector $subject): void
	{
		/** @var IQueueManager $config */
		$manager = $this->getMockBuilder(IQueueManager::class)->getMock();
		
		$this->toConnector->expects($this->once())->method('manager')->with('abc')->willReturn($manager);
		
		self::assertSame($manager, $subject->manager('abc'));
	}
	
	
	public function test_getQueue_SoftSwitchInstanceReturned(SoftSwitchConnector $subject): void
	{
		self::assertInstanceOf(SoftSwitchQueue::class, $subject->getQueue('abc'));
	}
	
	public function test_getQueue_SwiftSwitchHasBothQueues(SoftSwitchConnector $subject): void
	{
		/** @var IQueueConfig $config */
		$config = $this->getMockBuilder(IQueueConfig::class)->getMock();
		
		$this->fromQueue->expects($this->once())->method('dequeueWorkload')->willReturn([]);
		$this->toQueue->expects($this->once())->method('dequeueWorkload')->willReturn([]);
		
		$this->fromConnector->method('getQueue')->with('abc')->willReturn($this->fromQueue);
		$this->toConnector->method('getQueue')->with('abc')->willReturn($this->toQueue);
		
		$queue = $subject->getQueue('abc');
		
		$queue->dequeueWorkload(123, $config);
	}
}