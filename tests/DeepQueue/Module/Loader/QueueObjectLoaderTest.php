<?php
namespace DeepQueue\Module\Loader;


use DeepQueue\DeepQueue;
use DeepQueue\Enums\QueueLoaderPolicy;
use DeepQueue\Manager\QueueObject;
use DeepQueue\Plugins\InMemoryConnector\Base\IInMemoryQueueStorage;
use DeepQueue\Plugins\InMemoryConnector\Storage\InMemoryQueueStorage;
use DeepQueue\Plugins\InMemoryManager\InMemoryManager;
use DeepQueue\PreparedConfiguration\PreparedQueueSetup;

use DeepQueue\Scope;
use PHPUnit\Framework\TestCase;

use Serialization\Json\Serializers\ArraySerializer;
use Serialization\Json\Serializers\PrimitiveSerializer;
use Serialization\Serializers\JsonSerializer;
use Skeleton\Skeleton;
use Skeleton\Type;


class QueueObjectLoaderTest extends TestCase
{
	private function getDeepQueue($policy): DeepQueue
	{
		$dq = PreparedQueueSetup::InMemory((new JsonSerializer())->add(new ArraySerializer())->add(new PrimitiveSerializer()));
		$dq->config()->setQueueNotExistsPolicy($policy);
		
		return $dq;
	}
	
	private function getSubject($policy = QueueLoaderPolicy::CREATE_NEW): QueueObjectLoader
	{
		$dq = $this->getDeepQueue($policy);
		
		$loader = new QueueObjectLoader($dq->config()->manager(), 
			'loadertest', $policy);
		
		return $loader;
	}
	

	public function test_load_canCreateNewQueue_QueueNotExist()
	{
		$loader = $this->getSubject();
		
		$queue = $loader->load();
		
		self::assertInstanceOf(QueueObject::class, $queue);
		
		$this->getDeepQueue(QueueLoaderPolicy::CREATE_NEW)->config()->manager()->delete($queue->Id);
	}

	public function test_load_cantCreateNewQueue_QueueNotExist()
	{
		$loader = $this->getSubject(QueueLoaderPolicy::FORBIDDEN);
		
		self::assertNull($loader->load());
	}
	
	public function test_load_QueueExist()
	{
		$loader = $this->getSubject(QueueLoaderPolicy::CREATE_NEW);
		
		$q = $loader->load();
		
		$loader2 = $this->getSubject(QueueLoaderPolicy::FORBIDDEN);
		
		self::assertInstanceOf(QueueObject::class, $loader2->load());
	}
	
	public function test_require_canCreateNewQueue_QueueNotExist()
	{
		$loader = $this->getSubject();
		
		$queue = $loader->require();
		
		self::assertInstanceOf(QueueObject::class, $queue);
		
		$this->getDeepQueue(QueueLoaderPolicy::CREATE_NEW)->config()->manager()->delete($queue->Id);
	}

	/**
	 * @expectedException \DeepQueue\Exceptions\QueueNotExistsException
	 */
	public function test_require_cantCreateNewQueue_QueueNotExist()
	{
		$loader = $this->getSubject(QueueLoaderPolicy::FORBIDDEN);
		
		self::assertNull($loader->require());
	}
	
	public function test_require_QueueExist()
	{
		$loader = $this->getSubject(QueueLoaderPolicy::CREATE_NEW);
		
		$q = $loader->require();
		
		$loader2 = $this->getSubject(QueueLoaderPolicy::FORBIDDEN);
		
		self::assertInstanceOf(QueueObject::class, $loader2->require());
	}
}