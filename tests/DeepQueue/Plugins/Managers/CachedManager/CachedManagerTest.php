<?php
namespace DeepQueue\Plugins\Managers\CachedManager;


use DeepQueue\DeepQueue;
use DeepQueue\Base\IQueueObject;
use DeepQueue\Base\Plugins\IManagerPlugin;
use DeepQueue\Enums\Policy;
use DeepQueue\Enums\QueueLoaderPolicy;
use DeepQueue\Plugins\Managers\CachedManager\Base\ICachedManager;
use DeepQueue\Utils\RedisConfigParser;
use DeepQueue\Manager\QueueConfig;
use DeepQueue\Manager\QueueObject;
use DeepQueue\Utils\TimeBasedRandomIdGenerator;
use DeepQueue\Plugins\Connectors\InMemoryConnector\InMemoryConnector;
use DeepQueue\Plugins\Managers\RedisManager\RedisManager;

use PHPUnit\Framework\TestCase;

use Predis\Client;

use Serialization\Json\Serializers\ArraySerializer;
use Serialization\Json\Serializers\PrimitiveSerializer;
use Serialization\Serializers\JsonSerializer;


class CachedManagerTest extends TestCase
{
	private const MAIN_BUCKET = 'cachedtest:main';
	private const CACHE_BUCKET = 'cachedtest:cache';
	
	
	private function getSubject(): IManagerPlugin
	{
		$mainRedis = new RedisManager(['prefix' => self::MAIN_BUCKET]);
		$cacheRedis = new RedisManager(['prefix' => self::CACHE_BUCKET]);
		
		$cachedManager = new CachedManager($mainRedis, $cacheRedis);
		
		$dq = new DeepQueue();
		
		$dq->config()
			->setManagerPlugin($cachedManager)
			->setQueueNotExistsPolicy(QueueLoaderPolicy::CREATE_NEW)
			->setConnectorPlugin(new InMemoryConnector())
			->setSerializer((new JsonSerializer())->add(new ArraySerializer())->add(new PrimitiveSerializer()));
		
		return $dq->config()->manager();
	}
	
	private function getClient(string $prefix = 'cachedtest:main'): Client
	{
		$config = [];
		$config['prefix'] = $prefix;
		
		$config = RedisConfigParser::parse($config);
		
		return new Client($config->getParameters(), $config->getOptions());
	}
	
	
	public function setUp()
	{
		$this->getClient()->eval("return redis.call('del', 'defaultKey', unpack(redis.call('keys', ARGV[1])))", 
			0, self::MAIN_BUCKET . ':*');
		
		$this->getClient()->eval("return redis.call('del', 'defaultKey', unpack(redis.call('keys', ARGV[1])))", 
			0, self::CACHE_BUCKET . ':*');
	}
	

	public function test_createQueueObjectNotInCache_returnQueueObjectInCache()
	{
		$object = new QueueObject();
		$object->Name = 'createtest';
		
		$objectConfig = new QueueConfig();
		$objectConfig->UniqueKeyPolicy = Policy::ALLOWED;
		$objectConfig->DelayPolicy = Policy::IGNORED;
		$objectConfig->MaxBulkSize = 256;
		
		$object->Config = $objectConfig;
		
		$this->getSubject()->create($object);
		
		$cacheData = $this->getClient(self::CACHE_BUCKET)->hgetall('queue:createtest');
		
		self::assertEmpty($cacheData);
		
		$savedObject = $this->getSubject()->load($object->Name);
		
		self::assertEquals($object->Name, $savedObject->Name);
		
		$cacheData = $this->getClient(self::CACHE_BUCKET)->hgetall('queue:createtest');
		
		self::assertEquals($object->Name, $cacheData['Name']);
	}
	
	public function test_loadQueueObject_notExistsCantCreateAndInCacheToo_ReturnNull()
	{
		$manager = $this->getSubject();
		
		self::assertNull($manager->load('notexist', false));
		
		$cacheData = $this->getClient(self::CACHE_BUCKET)->hgetall('queue:notexist');
		
		self::assertEmpty($cacheData);
	}
	
	public function test_loadQueueObject_notExistsCanCreate_ReturnQueueObjectPutInCache()
	{
		$manager = $this->getSubject();
		
		self::assertInstanceOf(QueueObject::class, $manager->load('cancreate', true));
		
		$cacheData = $this->getClient(self::CACHE_BUCKET)->hgetall('queue:cancreate');
		
		self::assertEquals('cancreate', $cacheData['Name']);
	}
	
	public function test_loadQueueObject_exists_ReturnQueueObjectFromCache()
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
		$this->getSubject()->load($object->Name);
		
		$this->getClient(self::MAIN_BUCKET)->del(['queue:created']);
		
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
	
	public function test_update_notExistsNotPutInCache_ReturnQueueObjectPutInCache()
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
		
		$cacheData = $this->getClient(self::CACHE_BUCKET)->hgetall('queue:updated');
		
		self::assertEmpty($cacheData);
		
		self::assertNotEquals($updated->Id, $manager->load('updated', true)->Id);
		
		$cacheData = $this->getClient(self::CACHE_BUCKET)->hgetall('queue:updated');
		
		self::assertNotEmpty($cacheData);
	}
	
	public function test_updateRemoveFromCache_Exists_ReturnQueueObjectPutInCache()
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
		
		$cacheData = $this->getClient(self::CACHE_BUCKET)->hgetall('queue:updated');
		
		self::assertEmpty($cacheData);
		
		self::assertEquals($updated->Id, $manager->load('updated')->Id);
		
		$cacheData = $this->getClient(self::CACHE_BUCKET)->hgetall('queue:updated');
		
		self::assertEquals($updated->Id, $cacheData['Id']);
	}
	
	public function test_deleteByObjectDeleteFromCache_Exists()
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
		
		$manager->load($object->Name);
		
		$cacheData = $this->getClient(self::CACHE_BUCKET)->hgetall('queue:deleted1');
		
		self::assertEquals($object->Id, $cacheData['Id']);
		
		$manager->delete($object);
		
		self::assertNull($manager->load('deleted', false));
		
		$cacheData = $this->getClient(self::CACHE_BUCKET)->hgetall('queue:deleted1');
		
		self::assertEmpty($cacheData);
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
		
		$manager->load($object->Name);
		
		$cacheData = $this->getClient(self::CACHE_BUCKET)->hgetall('queue:deleted2');
		
		self::assertEquals($object->Id, $cacheData['Id']);
		
		$manager->delete($object->Id);
		
		self::assertNull($manager->load('deleted', false));
		
		$cacheData = $this->getClient(self::CACHE_BUCKET)->hgetall('queue:deleted2');
		
		self::assertEmpty($cacheData);
	}
	
	public function test_setTTL_QueueNotExistInCacheAfterTTL()
	{
		/** @var ICachedManager $manager */
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
		$manager->load($object->Name);
		
		$cacheData = $this->getClient(self::CACHE_BUCKET)->hgetall('queue:ttltest');
		
		self::assertNotEmpty($cacheData);
		
		sleep(3);
		
		$cacheData = $this->getClient(self::CACHE_BUCKET)->hgetall('queue:ttltest');
		
		self::assertEmpty($cacheData);
		
		self::assertEquals($object->Id, $manager->load('ttltest')->Id);
	}
}