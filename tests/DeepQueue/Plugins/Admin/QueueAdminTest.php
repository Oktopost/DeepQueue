<?php
namespace DeepQueue\Plugins\Admin;


use DeepQueue\Base\IMetaData;
use DeepQueue\DeepQueue;
use DeepQueue\Base\Plugins\IAdminPlugin;

use DeepQueue\Enums\QueueLoaderPolicy;
use DeepQueue\Enums\QueueState;
use DeepQueue\Manager\QueueConfig;
use DeepQueue\Manager\QueueObject;
use DeepQueue\Plugins\Connectors\InMemoryConnector\InMemoryConnector;
use DeepQueue\Plugins\Managers\InMemoryManager\Base\IInMemoryManagerStorage;
use DeepQueue\Plugins\Managers\InMemoryManager\InMemoryManager;
use DeepQueue\Plugins\Managers\InMemoryManager\Storage\InMemoryManagerStorage;
use DeepQueue\Scope;
use PHPUnit\Framework\TestCase;
use Serialization\Json\Serializers\PrimitiveSerializer;
use Serialization\Serializers\JsonSerializer;
use Skeleton\Skeleton;
use Skeleton\Type;


class QueueAdminTest extends TestCase
{
	private function getDQ(): DeepQueue
	{
		$dq = new DeepQueue();
		$dq->config()
			->setSerializer((new JsonSerializer())->add(new PrimitiveSerializer()))
			->setConnectorPlugin(new InMemoryConnector())
			->setManagerPlugin(new InMemoryManager())
			->setQueueNotExistsPolicy(QueueLoaderPolicy::FORBIDDEN);
		
		return $dq;
	}
	
	private function getSubject(): IAdminPlugin
	{
		return new QueueAdmin($this->getDQ());
	}
	
	
	protected function setUp()
	{
		/** @var Skeleton $skeleton */
		$skeleton = Scope::skeleton();
		$skeleton->override(IInMemoryManagerStorage::class, InMemoryManagerStorage::class, Type::Instance);
	}
	
	
	public function test_getQueues_NoQueues_ReturnEmptyArray()
	{
		self::assertEmpty($this->getSubject()->getQueues());
	}
	
	public function test_getQueues_QueuesExists_ReturnNotEmptyArray()
	{
		$q = new QueueObject();
		$q->Name = 'existingQ';
		$q->Config = new QueueConfig();
		
		$this->getDQ()->config()->manager()->create($q);
		
		$queues = $this->getSubject()->getQueues();
		
		self::assertNotEmpty($queues);
		self::assertEquals($q->Name, $queues[0]->Name);
	}
	
	public function test_getMetaData_NoQueueExist_ReturnNull()
	{
		self::assertNull($this->getSubject()->getMetaData('not-existing-q'));
	}
	
	public function test_getMetaData_QueueExist_ReturnIMetaData()
	{
		$q = new QueueObject();
		$q->Name = 'existingQ';
		$q->Config = new QueueConfig();
		
		$this->getDQ()->config()->manager()->create($q);
		
		self::assertInstanceOf(IMetaData::class, $this->getSubject()->getMetaData($q->Id));
	}
	
	public function test_updateState_QueueNotExist_ReturnFalse()
	{
		self::assertFalse($this->getSubject()->updateState('not-existing-q', QueueState::STOPPED));
	}
	
	public function test_updateState_QueueExist_ReturnTrue()
	{
		$q = new QueueObject();
		$q->Name = 'existingQ';
		$q->Config = new QueueConfig();
		
		$this->getDQ()->config()->manager()->create($q);
		
		self::assertTrue($this->getSubject()->updateState($q->Id, QueueState::STOPPED));
		
		$q = $this->getSubject()->getQueue($q->Id);
		
		self::assertEquals(QueueState::STOPPED, $q->State);
	}
}