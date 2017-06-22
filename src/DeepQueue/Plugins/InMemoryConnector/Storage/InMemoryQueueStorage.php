<?php
namespace DeepQueue\Plugins\InMemoryConnector\Storage;


use DeepQueue\Plugins\InMemoryConnector\Base\IInMemoryQueueStorage;


/**
 * @autoload
 */
class InMemoryQueueStorage implements IInMemoryQueueStorage
{
	/** @var string[]|array */
	private $_payloads = [];
	
	
	public function pushPayloads(string $queueName, array $payloads): array
	{
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
	
	public function countEnqueued(string $queueName): int
	{
		return sizeof($this->_payloads[$queueName]);
	}
}