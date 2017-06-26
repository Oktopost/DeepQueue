<?php
namespace DeepQueue\Plugins\RedisManager\DAO;


use DeepQueue\Base\IQueueConfig;
use DeepQueue\Base\IQueueObject;
use DeepQueue\Manager\QueueObject;
use DeepQueue\Base\Config\IRedisConfig;
use DeepQueue\Plugins\RedisManager\Base\IRedisManagerDAO;

use Serialization\Base\IJsonSerializer;
use Serialization\Serializers\JsonSerializer;
use Serialization\Json\Serializers\LiteObjectSerializer;

use Predis\Client;


class RedisManagerDAO implements IRedisManagerDAO
{
	private const ID_KEY_PREFIX		= 'queue.key';
	private const NAME_KEY_PREFIX	= 'queue';
	
	
	/** @var Client	*/
	private $client;
	
	/** @var IJsonSerializer */
	private $serializer;
	
	private $ttl = null;


	private function getIdKey(string $id): string
	{
		return self::ID_KEY_PREFIX . ":{$id}";
	}

	private function getNameKey(string $name): string 
	{
		return self::NAME_KEY_PREFIX . ":{$name}";
	}
	
	private function serializeConfig(IQueueConfig $config): string
	{
		return $this->serializer->serialize($config);
	}
	
	private function deserializeConfig(string $configData): IQueueConfig
	{
		return  $this->serializer->deserialize($configData);
	}

	private function prepareData(IQueueObject $queueObject): array
	{
		return [
			'Id' 		=> $queueObject->Id,
			'Name'		=> $queueObject->Name,
			'State'		=> $queueObject->State,
			'Config'	=> $this->serializeConfig($queueObject->Config)
		];
	}

	private function prepareObject(array $data): IQueueObject
	{
		$object = new QueueObject();
		$object->Id = $data['Id'];
		$object->Name = $data['Name'];
		$object->State = $data['State'];
		$object->Config = $this->deserializeConfig($data['Config']);
		
		return $object;
	}


	public function __construct()
	{
		$this->serializer = new JsonSerializer();
		$this->serializer->add(new LiteObjectSerializer());
	}

	
	public function setTTL(int $seconds): void
	{
		$this->ttl = $seconds;
	}

	public function initClient(IRedisConfig $config)
	{
		$this->client = new Client($config->getParameters(), $config->getOptions());
	}

	public function upsert(IQueueObject $queue): IQueueObject
	{
		$data = $this->prepareData($queue);

		$pipeline = $this->client->pipeline();
		
		$pipeline->set($this->getIdKey($data['Id']), $data['Name']);
		$pipeline->hmset($this->getNameKey($data['Name']), $data);
		
		if($this->ttl > 0)
		{
			$pipeline->expire($this->getIdKey($data['Id']), $this->ttl);
			$pipeline->expire($this->getNameKey($data['Name']), $this->ttl);
		}
		
		$pipeline->execute();

		return $queue;
	}

	public function load(string $queueName): ?IQueueObject
	{
		$data = $this->client->hgetall($this->getNameKey($queueName));

		if (!$data)
			return null;
		
		return $this->prepareObject($data);
	}
	
	public function delete(string $queueId): void
	{
		$name = $this->client->get($this->getIdKey($queueId));
		
		if (!$name)
			return;
		
		$this->client->pipeline()
			->del([$this->getNameKey($name), $this->getIdKey($queueId)])
			->execute();
	}
}