<?php
namespace DeepQueue\Plugins\InMemoryManager\Storage;


use DeepQueue\Base\IQueueObject;
use DeepQueue\Plugins\InMemoryManager\Queue\InMemoryQueue;
use DeepQueue\Plugins\InMemoryManager\Base\Storage\IInMemoryStorage;


/**
 * @autoload
 */
class InMemoryStorage implements IInMemoryStorage
{
	/** @var InMemoryQueue[]|array  */
	private $_queues = [];
	
	/** @var string[]|array */
	private $_payloads = [];
	
	
	public function pushQueue(IQueueObject $queue): IQueueObject
	{
		$this->_queues[$queue->Name] = $queue;
		
		return $this->_queues[$queue->Name];
	}

	public function pullQueue(string $name): ?IQueueObject
	{
		return isset($this->_queues[$name]) ? $this->_queues[$name] : null;
	}

	public function pushPayloads(string $queueName, array $payloads): array
	{
		if (!isset($this->_queues[$queueName]))
			return [];
		
		if (!isset($this->_payloads[$queueName]))
		{
			$this->_payloads[$queueName] = [];
		}
		
		$this->_payloads[$queueName] = array_merge($this->_payloads[$queueName], $payloads);
		
		return []; //TODO: return keys of $payloads
	}

	public function pullPayloads(string $queueName, int $count): array
	{
		if (!isset($this->_payloads[$queueName]) || !$this->_payloads[$queueName])
		{
			return [];
		}
		
		return array_slice($this->_payloads[$queueName], 0, $count);
	}
	
	public function deletePayload(string $queueName, string $key): bool
	{
		if (!isset($this->_payloads[$queueName]) || !isset($this->_payloads[$queueName][$key]))
		{
			return false;
		}
		
		unset($this->_payloads[$queueName][$key]);
		
		return true;
	}
}