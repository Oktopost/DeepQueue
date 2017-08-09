<?php
namespace DeepQueue\Plugins\Managers\RedisManager;


use DeepQueue\DeepQueue;
use DeepQueue\Enums\Policy;
use DeepQueue\Enums\QueueLoaderPolicy;
use DeepQueue\Base\IQueueObject;
use DeepQueue\Base\Config\IRedisConfig;
use DeepQueue\Base\Plugins\IManagerPlugin;
use DeepQueue\Payload;
use DeepQueue\Plugins\Connectors\RedisConnector\RedisConnector;
use DeepQueue\Utils\RedisConfigParser;
use DeepQueue\Manager\QueueConfig;
use DeepQueue\Manager\QueueObject;
use DeepQueue\Utils\TimeBasedRandomIdGenerator;
use DeepQueue\Plugins\Managers\RedisManager\Base\IRedisManager;
use DeepQueue\Plugins\Connectors\InMemoryConnector\InMemoryConnector;

use Predis\Client;

use Serialization\Serializers\JsonSerializer;
use Serialization\Json\Serializers\ArraySerializer;
use Serialization\Json\Serializers\PrimitiveSerializer;

use PHPUnit\Framework\TestCase;


class RedisManagerTest extends TestCase
{
	private function getConfig(): IRedisConfig
	{
		$config = [];
		$config['prefix'] = 'test.manager.deepqueue';
		
		return RedisConfigParser::parse($config);
	}
	
	private function getDQ(): DeepQueue
	{
		$dq = new DeepQueue();
		
		$dq->config()
			->setManagerPlugin(new RedisManager($this->getConfig()))	
			->setQueueNotExistsPolicy(QueueLoaderPolicy::CREATE_NEW)
			->setConnectorPlugin(new RedisConnector($this->getConfig()))
			->setSerializer((new JsonSerializer())->add(new ArraySerializer())->add(new PrimitiveSerializer()));
		
		return $dq;
	}
	
	private function getSubject(): IManagerPlugin
	{
		return $this->getDQ()->config()->manager();
	}
	
	private function getClient(): Client
	{
		$config = $this->getConfig();
		
		return new Client($config->getParameters(), $config->getOptions());
	}
	
	
	public function setUp()
	{
		$this->getClient()->eval("return redis.call('del', 'defaultKey', unpack(redis.call('keys', ARGV[1])))", 
			0, 'test.manager.deepqueue:*');
	}


	public function test_createQueueObject_returnQueueObject()
	{
		$object = new QueueObject();
		$object->Name = 'createtest';
		
		$objectConfig = new QueueConfig();
		$objectConfig->UniqueKeyPolicy = Policy::ALLOWED;
		$objectConfig->DelayPolicy = Policy::IGNORED;
		$objectConfig->MaxBulkSize = 256;
		
		$object->Config = $objectConfig;
		
		$this->getSubject()->create($object);
		
		$savedObject = $this->getSubject()->load($object->Name);
		
		self::assertEquals($object->Name, $savedObject->Name);
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
		$object->Id = (new TimeBasedRandomIdGenerator())->get();
		$object->Name = 'created';
		
		$objectConfig = new QueueConfig();
		$objectConfig->UniqueKeyPolicy = Policy::ALLOWED;
		$objectConfig->DelayPolicy = Policy::IGNORED;
		$objectConfig->MaxBulkSize = 256;
		
		$object->Config = $objectConfig;
		
		$this->getSubject()->create($object);
		
		self::assertEquals($object->Id, $manager->load('created')->Id);
	}
	
	public function test_loadById_notExits_ReturnNull()
	{
		self::assertNull($this->getSubject()->loadById('not-existing-id'));
	}
	
	public function test_loadById_Exists_ReturnQueueObject()
	{
		$object = new QueueObject();
		$object->Id = 'test-id';
		$object->Name = 'created';
		$object->Config = new QueueConfig();
		
		$this->getSubject()->create($object);
		
		$loadedObject = $this->getSubject()->loadById($object->Id);
		
		self::assertInstanceOf(IQueueObject::class, $loadedObject);
		self::assertEquals($object->Id, $loadedObject->Id);
	}
	
	public function test_loadAll_NoQueues_ReturnEmptyArray()
	{
		self::assertEmpty($this->getSubject()->loadAll());
	}
	
	public function test_loadAll_QueuesExist_ReturnArray()
	{
		$object = new QueueObject();
		$object->Id = 'test-id';
		$object->Name = 'created';
		$object->Config = new QueueConfig();
		
		$this->getSubject()->create($object);
		
		$queues = $this->getSubject()->loadAll();
		
		self::assertNotEmpty($queues);
		self::assertEquals($object->Id, $queues[0]->Id);
	}
	
	public function test_update_notExists_ReturnQueueObject()
	{
		$manager = $this->getSubject();
		
		$object = new QueueObject();
		$object->Id = (new TimeBasedRandomIdGenerator())->get();
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
		$object->Id = (new TimeBasedRandomIdGenerator())->get();
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
		$object->Id = (new TimeBasedRandomIdGenerator())->get();
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
		$object->Id = (new TimeBasedRandomIdGenerator())->get();
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
		$object->Id = (new TimeBasedRandomIdGenerator())->get();
		$object->Name = 'deleted3';
		
		$objectConfig = new QueueConfig();
		$objectConfig->UniqueKeyPolicy = Policy::ALLOWED;
		$objectConfig->DelayPolicy = Policy::IGNORED;
		$objectConfig->MaxBulkSize = 256;
		
		$object->Config = $objectConfig;
		
		$manager->delete($object->Id);
		
		self::assertNull($manager->load('deleted', false));
	}
	
	public function test_setTTL_QueueNotExistAfterTTL()
	{
		/** @var IRedisManager $manager */
		$manager = $this->getSubject();
		
		$object = new QueueObject();
		$object->Id = (new TimeBasedRandomIdGenerator())->get();
		$object->Name = 'ttltest';
		
		$objectConfig = new QueueConfig();
		$objectConfig->UniqueKeyPolicy = Policy::ALLOWED;
		$objectConfig->DelayPolicy = Policy::IGNORED;
		$objectConfig->MaxBulkSize = 256;
		
		$object->Config = $objectConfig;
		
		$manager->setTTL(2);
		$manager->create($object);
		
		sleep(3);
		
		self::assertNull($manager->load('ttltest'));
	}
	
	public function test_flushCache_QueueNotExistInCacheAfterFlush_QueuedDataNotAffected()
	{
		/** @var IRedisManager $manager */
		$manager = $this->getSubject();
		
		$object = new QueueObject();
		$object->Id = (new TimeBasedRandomIdGenerator())->get();
		$object->Name = 'flushtest';
		
		$objectConfig = new QueueConfig();
		$objectConfig->UniqueKeyPolicy = Policy::ALLOWED;
		$objectConfig->DelayPolicy = Policy::IGNORED;
		$objectConfig->MaxBulkSize = 256;
		
		$object->Config = $objectConfig;
		
		$manager->create($object);
		
		$payload = new Payload('staying-alive');
		$payload->Key = 'staying-alive';
		
		$this->getDQ()->get($object->Name)->enqueue($payload);
			
		$manager->flushCache();
	
		self::assertNull($manager->load('flushtest'));
		
		$payload = $this->getDQ()->get($object->Name)->dequeue(1);
		
		self::assertEquals('staying-alive', $payload[0]);
	}
}