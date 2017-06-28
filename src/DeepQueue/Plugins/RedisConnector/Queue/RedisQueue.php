<?php
namespace DeepQueue\Plugins\RedisConnector\Queue;


use DeepQueue\Utils\PayloadConverter;
use DeepQueue\Base\Queue\Remote\IRemoteQueue;
use DeepQueue\Plugins\RedisConnector\Base\IRedisQueueDAO;

use Serialization\Base\ISerializer;


class RedisQueue implements IRemoteQueue
{
	private $name;

	/** @var IRedisQueueDAO */
	private $dao;
	
	/** @var PayloadConverter */
	private $converter;
	
	
	public function __construct(string $name, IRedisQueueDAO $dao, ISerializer $serializer)
	{
		$this->dao = $dao;
		$this->converter = new PayloadConverter($serializer);
		$this->name = $name;
	}
	
	
	public function dequeueWorkload(int $count = 1, ?float $waitSeconds = null): array
	{
		if ($count <= 0)
		{
			return [];
		}
		
		$dequeuer = new RedisDequeue($this->dao, $this->name);
		
		$payloads = $dequeuer->dequeue($count, round($waitSeconds));
		
		return $this->converter->getWorkloads($payloads);
	}

	public function enqueue(array $payload): array
	{
		$prepared = $this->converter->prepareAll($payload);
		
		return $this->dao->enqueue($this->name, $prepared);
	}
}