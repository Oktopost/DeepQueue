<?php
namespace DeepQueue\Plugins\Connectors\InMemoryConnector\Storage;


use DeepQueue\Plugins\Connectors\InMemoryConnector\Base\IInMemoryQueueStorage;
use DeepQueue\Scope;
use PHPUnit\Framework\TestCase;


class InMemoryQueueStorageTest extends TestCase
{
	private function getSubject(): IInMemoryQueueStorage
	{
		return Scope::skeleton(IInMemoryQueueStorage::class);
	}
	
	
	protected function setUp()
	{
		/** @var IInMemoryQueueStorage $storage */
		$storage = Scope::skeleton(IInMemoryQueueStorage::class);
		$storage->flushCache();
	}
	
	public function test_pushPayloads_notExistingQueue_GotKeys()
	{
		$ids = $this->getSubject()->pushPayloads('test', ['a' => [1,2,3]]);
		
		self::assertEquals('a', $ids[0]);
	}
	
	public function test_pushPayloads_existingQueue_GotKeys()
	{
		$this->getSubject()->pushPayloads('test', ['a' => [1,2,3]]);
		$ids = $this->getSubject()->pushPayloads('test', ['b' => [1,2,3]]);
		
		self::assertEquals('b', $ids[0]);
	}
	
	public function test_pullPayloads_notExistingQueue_GotEmptyArray()
	{
		$payloads = $this->getSubject()->pullPayloads('not-existing-queue', 10);
		
		self::assertEmpty($payloads);
	}

	public function test_pullPayloads_existingNotEmptyQueue_GotPayloads()
	{
		$this->getSubject()->pushPayloads('test', ['a' => [1,2,3]]);
		$payloads = $this->getSubject()->pullPayloads('test', 1);
		
		self::assertEquals('a', array_keys($payloads)[0]);
	}
	
	public function test_pullPayloads_existingEmptyQueue_GotEmptyArray()
	{
		$this->getSubject()->pushPayloads('test', ['a' => [1,2,3]]);
		$this->getSubject()->pullPayloads('test', 1);
		
		$payloads = $this->getSubject()->pullPayloads('test', 1);
		
		self::assertEmpty($payloads);
	}

	public function test_countEnqueued_NotExistingQueue_ReturnZero()
	{
		$enqueued = $this->getSubject()->countEnqueued('not-exist');
		
		self::assertEquals(0, $enqueued);
	}
	
	public function test_countEnqueued_ExistingQueue_GotIntSize()
	{
		$this->getSubject()->pushPayloads('test', ['a' => [1,2,3]]);
		
		self::assertEquals(1, $this->getSubject()->countEnqueued('test'));
	}
	
	public function test_cache_GotCacheArray()
	{
		$this->getSubject()->pushPayloads('test', ['a' => [1,2,3]]);
		
		$cache = $this->getSubject()->cache();
		
		self::assertEquals(1, sizeof($cache));
		self::assertTrue(isset($cache['test']));
	}
	
	public function test_flushCache_GotEmptyCache()
	{
		$this->getSubject()->pushPayloads('test', ['a' => [1,2,3]]);
		$this->getSubject()->flushCache();
		
		self::assertEmpty($this->getSubject()->cache());
	}
}