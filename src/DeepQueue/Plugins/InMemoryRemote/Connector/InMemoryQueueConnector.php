<?php
namespace DeepQueue\Plugins\InMemoryRemote\Connector;


use DeepQueue\Plugins\InMemoryRemote\Base\IInMemoryQueueConnector;


/**
 * @autoload
 */
class InMemoryQueueConnector implements IInMemoryQueueConnector
{
	/**
	 * @autoload
	 * @var \DeepQueue\Plugins\InMemoryRemote\Base\IInMemoryRemoteStorage
	 */
	private $storage;
	
	
	private function getUnsetted(string $queueName, array $payloads): array 
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
	
	
	public function enqueue(string $queueName, array $payloads): array
	{
		return $this->storage->pushPayloads($queueName, $payloads);
	}

	public function dequeue(string $queueName, int $count = 1): array
	{
		$payloads = $this->storage->pullPayloads($queueName, $count);
		
		return $this->getUnsetted($queueName, $payloads);
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