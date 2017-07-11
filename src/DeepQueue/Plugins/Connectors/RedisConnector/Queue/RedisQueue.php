<?php
namespace DeepQueue\Plugins\Connectors\RedisConnector\Queue;


use DeepQueue\Payload;
use DeepQueue\Workload;
use DeepQueue\Base\Queue\Remote\IRemoteQueue;
use DeepQueue\Utils\PayloadConverter;
use DeepQueue\Plugins\Logger\Base\ILogger;
use DeepQueue\Plugins\Connectors\RedisConnector\Base\IRedisQueueDAO;

use Serialization\Base\ISerializer;


class RedisQueue implements IRemoteQueue
{
	private $name;

	/** @var IRedisQueueDAO */
	private $dao;
	
	/** @var PayloadConverter */
	private $converter;
	
	/** @var ILogger */
	private $logger;
	
	
	private function log(array $data, $operation): void
	{
		$message = ucfirst($operation) . " in {$this->name} queue payload with id: ";
		
		foreach ($data as $key => $payload)
		{
			$this->logger->info($message . $key, [
				'payload' 	=> $payload,
				'queue'		=> $this->name
			], $key, $this->name);
		}
	}
	
	
	public function __construct(string $name, IRedisQueueDAO $dao, ISerializer $serializer, ILogger $logger)
	{
		$this->dao = $dao;
		$this->name = $name;
		$this->converter = new PayloadConverter($serializer);
		$this->logger = $logger;
	}
	
	
	/**
	 * @return Workload[]|array
	 */
	public function dequeueWorkload(int $count = 1, ?float $waitSeconds = null): array
	{
		if ($count <= 0)
		{
			return [];
		}
		
		$dequeuer = new RedisDequeue($this->dao, $this->name);
		
		$payloads = $dequeuer->dequeue($count, round($waitSeconds));
		
		if ($payloads)
		{
			$this->log($payloads, 'dequeue');
		}
		
		return $this->converter->getWorkloads($payloads);
	}

	/**
	 * @param Payload[] $payload
	 * @return string[]|array IDs for each payload
	 */
	public function enqueue(array $payload): array
	{
		$prepared = $this->converter->prepareAll($payload);
		
		$ids = $this->dao->enqueue($this->name, $prepared);
		
		if ($ids && isset($prepared['keyValue']))
		{
			$this->log(array_intersect_key($prepared['keyValue'], array_flip($ids)), 'enqueue');
		}
		
		return $ids;
	}
}