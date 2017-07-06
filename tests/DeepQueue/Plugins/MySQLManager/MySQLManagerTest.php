<?php
namespace DeepQueue\Plugins\Managers\MySQLManager;

use DeepQueue\Base\Plugins\IManagerPlugin;

use DeepQueue\DeepQueue;
use DeepQueue\Enums\Policy;
use DeepQueue\Enums\QueueLoaderPolicy;
use DeepQueue\Manager\QueueConfig;
use DeepQueue\Manager\QueueObject;
use DeepQueue\Module\Ids\TimeBasedRandomGenerator;
use DeepQueue\Plugins\Connectors\InMemoryConnector\InMemoryConnector;
use lib\MySQLConfig;
use PHPUnit\Framework\TestCase;
use Serialization\Json\Serializers\ArraySerializer;
use Serialization\Json\Serializers\PrimitiveSerializer;
use Serialization\Serializers\JsonSerializer;


class MySQLManagerTest extends TestCase
{
	private const TABLENAME = 'DeepQueueObject';
	
	
	private function getSubject(): IManagerPlugin
	{
		$dq = new DeepQueue();
		
		$dq->config()
			->setManagerPlugin(new MySQLManager(MySQLConfig::get()))
			->setQueueNotExistsPolicy(QueueLoaderPolicy::CREATE_NEW)
			->setConnectorPlugin(new InMemoryConnector())
			->setSerializer((new JsonSerializer())->add(new ArraySerializer())->add(new PrimitiveSerializer()));
		
		return $dq->config()->manager();
	}
	
	
	protected function setUp()
	{
		MySQLConfig::connector()->delete()
			->where('1 = 1')
			->from(self::TABLENAME)
			->executeDml();
	}


	public function test_createQueueObject_returnQueueObject()
	{
		$object = new QueueObject();
		$object->Name = 'createtest';
		
		$objectConfig = new QueueConfig();
		$objectConfig->UniqueKeyPolicy = Policy::ALLOWED;
		$objectConfig->DelayPolicy = Policy::IGNORED;
		$objectConfig->MaxBulkSize = 111;
		
		$object->Config = $objectConfig;
		
		$this->getSubject()->create($object);
		
		$savedObject = $this->getSubject()->load($object->Name);
		
		self::assertEquals($object->Name, $savedObject->Name);
		self::assertEquals(111, $object->Config->MaxBulkSize);
	}
	
	public function test_loadQueueObject_notExistsCantCreate_ReturnNull()
	{
		$manager = $this->getSubject();
		
		self::assertNull($manager->load('notexist', false));
	}
	
	public function test_loadQueueObject_notExistsCanCreate_ReturnQueueObject()
	{
		$manager = $this->getSubject();
		
		self::assertInstanceOf(QueueObject::class, $manager->load('cancreate', true));
	}
	
	public function test_loadQueueObject_exists_ReturnQueueObject()
	{
		$manager = $this->getSubject();
		
		$object = new QueueObject();
		$object->Id = (new TimeBasedRandomGenerator())->get();
		$object->Name = 'created';
		
		$objectConfig = new QueueConfig();
		$objectConfig->UniqueKeyPolicy = Policy::ALLOWED;
		$objectConfig->DelayPolicy = Policy::IGNORED;
		$objectConfig->MaxBulkSize = 256;
		
		$object->Config = $objectConfig;
		
		$this->getSubject()->create($object);
		
		self::assertEquals($object->Id, $manager->load('created')->Id);
	}
	
	public function test_update_notExists_ReturnQueueObject()
	{
		$manager = $this->getSubject();
		
		$object = new QueueObject();
		$object->Id = (new TimeBasedRandomGenerator())->get();
		$object->Name = 'updated';
		
		$objectConfig = new QueueConfig();
		$objectConfig->UniqueKeyPolicy = Policy::ALLOWED;
		$objectConfig->DelayPolicy = Policy::IGNORED;
		$objectConfig->MaxBulkSize = 256;
		
		$object->Config = $objectConfig;
		
		$updated = $manager->update($object);
		
		self::assertNotEquals($updated->Id, $manager->load('updated', true)->Id);
	}
	
	public function test_update_Exists_ReturnQueueObject()
	{
		$manager = $this->getSubject();
		
		$object = new QueueObject();
		$object->Id = (new TimeBasedRandomGenerator())->get();
		$object->Name = 'updated';
		
		$objectConfig = new QueueConfig();
		$objectConfig->UniqueKeyPolicy = Policy::ALLOWED;
		$objectConfig->DelayPolicy = Policy::IGNORED;
		$objectConfig->MaxBulkSize = 256;
		
		$object->Config = $objectConfig;
		
		$manager->create($object);
		
		$updated = $manager->update($object);
		
		self::assertEquals($updated->Id, $manager->load('updated')->Id);
	}
	
	public function test_deleteByObject_Exists()
	{
		$manager = $this->getSubject();
		
		$object = new QueueObject();
		$object->Id = (new TimeBasedRandomGenerator())->get();
		$object->Name = 'deleted1';
		
		$objectConfig = new QueueConfig();
		$objectConfig->UniqueKeyPolicy = Policy::ALLOWED;
		$objectConfig->DelayPolicy = Policy::IGNORED;
		$objectConfig->MaxBulkSize = 256;
		
		$object->Config = $objectConfig;
		
		$manager->create($object);
		
		$manager->delete($object);
		
		self::assertNull($manager->load('deleted', false));
	}
	
	public function test_deleteByName_Exists()
	{
		$manager = $this->getSubject();
		
		$object = new QueueObject();
		$object->Id = (new TimeBasedRandomGenerator())->get();
		$object->Name = 'deleted2';
		
		$objectConfig = new QueueConfig();
		$objectConfig->UniqueKeyPolicy = Policy::ALLOWED;
		$objectConfig->DelayPolicy = Policy::IGNORED;
		$objectConfig->MaxBulkSize = 256;
		
		$object->Config = $objectConfig;
		
		$manager->create($object);
		
		$manager->delete($object->Id);
		
		self::assertNull($manager->load('deleted', false));
	}
	
	public function test_deleteByName_NotExists()
	{
		$manager = $this->getSubject();
		
		$object = new QueueObject();
		$object->Id = (new TimeBasedRandomGenerator())->get();
		$object->Name = 'deleted3';
		
		$objectConfig = new QueueConfig();
		$objectConfig->UniqueKeyPolicy = Policy::ALLOWED;
		$objectConfig->DelayPolicy = Policy::IGNORED;
		$objectConfig->MaxBulkSize = 256;
		
		$object->Config = $objectConfig;
		
		$manager->delete($object->Id);
		
		self::assertNull($manager->load('deleted', false));
	}
}