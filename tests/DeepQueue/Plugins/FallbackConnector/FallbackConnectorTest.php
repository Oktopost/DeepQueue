<?php
namespace DeepQueue\Plugins\FallbackConnector;


use DeepQueue\Payload;
use DeepQueue\DeepQueue;
use DeepQueue\Enums\QueueLoaderPolicy;
use DeepQueue\Utils\RedisConfigParser;
use DeepQueue\Base\Config\IRedisConfig;
use DeepQueue\Plugins\InMemoryManager\InMemoryManager;
use DeepQueue\Plugins\InMemoryConnector\InMemoryConnector;
use DeepQueue\Plugins\RedisConnector\RedisConnector;
use DeepQueue\Plugins\RedisConnector\Helper\RedisNameBuilder;

use PHPUnit\Framework\TestCase;

use Predis\Client;

use Serialization\Serializers\JsonSerializer;
use Serialization\Json\Serializers\ArraySerializer;
use Serialization\Json\Serializers\PrimitiveSerializer;


class FallbackConnectorTest extends TestCase
{
	private const QUEUE_NAME = 'fbqueue';
	
	
	private function getConfig(): IRedisConfig
	{
		$config = [];
		$config['prefix'] = 'fallback.test';
		
		return RedisConfigParser::parse($config);
	}
	
	private function getDQ(): DeepQueue
	{
		$fallbackConnector = new FallbackConnector(new InMemoryConnector(true), 
			new RedisConnector($this->getConfig()));

		$dq = new DeepQueue();
	
		$dq->config()
			->setManagerPlugin(new InMemoryManager())
			->setQueueNotExistsPolicy(QueueLoaderPolicy::CREATE_NEW)
			->setSerializer((new JsonSerializer())->add(new PrimitiveSerializer())->add(new ArraySerializer()))
			->setConnectorPlugin($fallbackConnector);
		
		return $dq;
	}
	
	
	public function setUp()
	{
		$config = $this->getConfig();
		
		$client = new Client($config->getParameters(), $config->getOptions());

		$client->eval("return redis.call('del', 'defaultKey', unpack(redis.call('keys', ARGV[1])))", 
			0, 'fallback.test:*');
	}
	
	
	public function test_sanity()
	{
		$errorsCounter = 0;
		$loadedCounter = 0;
		
		$queue = $this->getDQ()->get(self::QUEUE_NAME);
		
		for ($i = 0; $i < 1000; $i++)
		{
			$payload1 = new Payload();
			$payload1->Payload = 'payloadone';
			
			$payload2 = new Payload();
			$payload2->Payload = [1,2,3];
			
			$queue->enqueueAll([$payload1, $payload2]);
			
			$workloads = $queue->dequeueWorkload(255);
			
			if (sizeof($workloads) == 0)
			{
				$errorsCounter++;
			}
			
			$loadedCounter += sizeof($workloads);
	
		}
		
		$meta = $this->getDQ()->getMetaData(self::QUEUE_NAME);
		
		$config = $this->getConfig();
		
		$client = new Client($config->getParameters(), $config->getOptions());
		
		$leftInFallback = $client->lrange(RedisNameBuilder::getNowKey(self::QUEUE_NAME), 0, 255);
		
		self::assertTrue($errorsCounter > 200);
		self::assertEquals(2000, $loadedCounter + $meta->Enqueued + sizeof($leftInFallback));
	}
	
	public function test_GetMetaData()
	{
		$payload1 = new Payload();
		$payload1->Payload = 'payloadone';
		
		$queue = $this->getDQ()->get(self::QUEUE_NAME);
		$queue->enqueue($payload1);
		
		$metaData = $this->getDQ()->getMetaData(self::QUEUE_NAME);
		$enqueued = $metaData->Enqueued;
		$wasError = false;
		
		for ($i = 0; $i < 10; $i++)
		{
			$metaData = $this->getDQ()->getMetaData(self::QUEUE_NAME);
			
			if ($metaData->Enqueued != $enqueued)
			{
				$wasError = true;
			}
		}
		
		self::assertTrue($wasError);
	}
}