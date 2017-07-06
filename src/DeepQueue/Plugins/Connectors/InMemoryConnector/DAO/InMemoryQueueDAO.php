<?php
namespace DeepQueue\Plugins\Connectors\InMemoryConnector\DAO;


use DeepQueue\Plugins\Connectors\InMemoryConnector\Base\IInMemoryQueueStorage;
use DeepQueue\Plugins\Connectors\InMemoryConnector\Base\IInMemoryQueueDAO;


class InMemoryQueueDAO implements IInMemoryQueueDAO
{
	/** @var IInMemoryQueueStorage */
	private $storage;
	
	
	private function getAvailable(string $queueName, array $payloads): array 
	{
		foreach ($payloads as $key => $payload)
		{
			if (!$this->delete($queueName, $key))
			{
				unset($payloads[$key]);
			}
		}
		
		return $payloads;
	}
	
	
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
		$payloads = $this->storage->pullPayloads($queueName, $count);
		
		return $this->getAvailable($queueName, $payloads);
	}
	
	public function delete(string $queueName, string $payloadId): bool
	{
		return $this->storage->deletePayload($queueName, $payloadId);
	}
	
	public function countEnqueued(string $queueName): int 
	{
		return $this->storage->countEnqueued($queueName);
	}
}