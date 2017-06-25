<?php
namespace DeepQueue\Plugins\RedisManager\Connector;


use DeepQueue\Base\IQueueObject;
use DeepQueue\Manager\QueueObject;
use DeepQueue\Plugins\RedisManager\Base\IRedisManagerConnector;

use Serialization\Serializers\JsonSerializer;
use Serialization\Json\Serializers\LiteObjectSerializer;

use Predis\Client;


class RedisManagerConnector implements IRedisManagerConnector
{
	/** @var Client	*/
	private $client;
	
	
	private function initClient()
	{
		$this->client = new Client('tcp://127.0.0.1:6379', ['prefix' => 'deepqueue:']);
	}
	
	private function getIdKey(string $id): string
	{
		return "queue.key:{$id}";
	}
	
	private function getNameKey(string $name): string 
	{
		return "queue:{$name}";
	}
	
	private function prepareData(IQueueObject $queueObject): array
	{
		return [
			'Id' 		=> $queueObject->Id,
			'Name'		=> $queueObject->Name,
			'State'		=> $queueObject->State,
			'Config'	=> (new JsonSerializer())->add(new LiteObjectSerializer())->serialize($queueObject->Config)
		];
	}
	
	private function prepareObject(array $data): IQueueObject
	{
		$config = (new JsonSerializer())->add(new LiteObjectSerializer())->deserialize($data['Config']);
		
		$object = new QueueObject();
		$object->Id = $data['Id'];
		$object->Name = $data['Name'];
		$object->State = $data['State'];
		$object->Config = $config;
		
		return $object;
	}
	
	
	public function __construct()
	{
		$this->initClient();
	}


	public function upsert(IQueueObject $queue): IQueueObject
	{
		$data = $this->prepareData($queue);
		
		$this->client->pipeline()
			->set($this->getIdKey($data['Id']), $data['Name'])
			->hmset($this->getNameKey($data['Name']), $data)
			->execute();
		
		//TODO: add to set for hold all queues (id/name?) in redis
		
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
		
		$this->client->del([$this->getNameKey($name), $this->getIdKey($queueId)]);
		
		//TODO: remove from set for hold all queues (id/name?) in redis
	}
}