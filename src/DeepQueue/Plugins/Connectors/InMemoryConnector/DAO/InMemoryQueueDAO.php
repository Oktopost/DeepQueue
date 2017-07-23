<?php
namespace DeepQueue\Plugins\Connectors\InMemoryConnector\DAO;


use DeepQueue\Plugins\Connectors\InMemoryConnector\Base\IInMemoryQueueDAO;
use DeepQueue\Plugins\Connectors\InMemoryConnector\Base\IInMemoryQueueStorage;


class InMemoryQueueDAO implements IInMemoryQueueDAO
{
	/** @var IInMemoryQueueStorage */
	private $storage;
	
	
	public function __construct(IInMemoryQueueStorage $storage)
	{
		$this->storage = $storage;
	}


	public function enqueue(string $queueName, array $payloads): array
	{
		return $this->storage->pushPayloads($queueName, $payloads);
	}

	public function dequeue(string $queueName, int $count = 1): array
	{
		if ($count <= 0)
		{
			return [];
		}
		
		return $this->storage->pullPayloads($queueName, $count);
	}
	
	public function countEnqueued(string $queueName): int 
	{
		return $this->storage->countEnqueued($queueName);
	}
}