<?php
namespace DeepQueue\Plugins\Managers\RedisManager\DAO;


use DeepQueue\Base\IQueueConfig;
use DeepQueue\Base\IQueueObject;
use DeepQueue\Manager\QueueObject;
use DeepQueue\Base\Config\IRedisConfig;
use DeepQueue\Plugins\Managers\RedisManager\Base\IRedisManagerDAO;

use Serialization\Base\IJsonSerializer;
use Serialization\Serializers\JsonSerializer;
use Serialization\Json\Serializers\LiteObjectSerializer;

use Predis\Client;


/**
 * @autoload
 */
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

	public function initClient(IRedisConfig $config): void
	{
		$this->client = new Client($config->getParameters(), $config->getOptions());
	}

	public function upsert(IQueueObject $queue): void
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
	}

	public function loadByName(string $queueName): ?IQueueObject
	{
		$data = $this->client->hgetall($this->getNameKey($queueName));

		return $data ? $this->prepareObject($data) : null;
	}

	public function load(string $id): ?IQueueObject
	{
		$name = $this->client->get($this->getIdKey($id));
		
		if (!$name)
			return null;
		
		$data = $this->client->hgetall($this->getNameKey($name));
		
		return $data ? $this->prepareObject($data) : null;
	}

	public function loadAll(): array
	{
		$keys = $this->client->keys(self::NAME_KEY_PREFIX . ':*');
		
		if (!$keys)
			return [];
		
		$queues = [];
		
		$prefix = $this->client->getOptions()->prefix->getPrefix();
		
		$pipeline = $this->client->pipeline();
		
		foreach ($keys as $key)
		{
			$unprefixKey = str_replace($prefix, '', $key);
			
			$pipeline->hgetall($unprefixKey);
		}
		
		$response = $pipeline->execute();
		
		foreach ($response as $item)
		{
			$queues[] = $this->prepareObject($item);
		}
		
		return $queues;
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