<?php
namespace DeepQueue\Plugins\Connectors\MySQLConnector\DAO;


use DeepQueue\Base\Plugins\ConnectorElements\IQueueManagerDAO;
use DeepQueue\Plugins\Connectors\MySQLConnector\Base\DAO\IMySQLQueueDAO;

use Squid\MySql\IMySqlConnector;


/**
 * @autoload
 */
class MySQLQueueDAO implements IMySQLQueueDAO, IQueueManagerDAO
{
	private const PAYLOAD_TABLE = 'DeepQueuePayload';
	private const ENQUEUE_TABLE = 'DeepQueueEnqueue';
	
	
	/** @var IMySqlConnector */
	private $connector = null;
	
	
	private function getIds(string $queueName, int $count, int $bufferDelay): array 
	{
		$time = time() - $bufferDelay;
		
		$now = date('Y-m-d H:i:s', $time);

		$ids = $this->connector
			->select()
			->from(self::ENQUEUE_TABLE)
			->column('Id')
			->byField('QueueName', $queueName)
			->where('DequeueTime <= ?', $now)
			->orderBy('DequeueTime')
			->limit(0, $count)
			->queryColumn();
		
		return $ids ?: [];
	}

	private function delete(string $queueName, array $payloadIds): bool
	{
		$this->connector
			->delete()
			->from(self::ENQUEUE_TABLE)
			->byFields(['Id' => $payloadIds, 'QueueName' => $queueName])
			->executeDml();

		$this->connector
			->delete()
			->from(self::PAYLOAD_TABLE)
			->byFields(['Id' => $payloadIds, 'QueueName' => $queueName])
			->executeDml();

		return true;
	}
	
	private function prepareResponse(array $element): array 
	{
		return [$element['Id'] => strtotime($element['DequeueTime']) * 1000];
	}
	
	private function isPackageReady(string $queueName, int $packageSize): bool
	{
		return $this->countDelayedReadyToDequeue($queueName) >= $packageSize;
	}
	
	private function isBufferOverflowed(string $queueName, int $buffer): bool
	{
		return $this->countDelayedReadyToDequeue($queueName, $buffer) > 0;
	}
	
	private function needToSetZeroBuffer(string $queueName, int $buffer, int $packageSize): bool
	{
		return (($packageSize > 0 && $this->isPackageReady($queueName, $packageSize)) ||
			$this->isBufferOverflowed($queueName, $buffer));
	}


	/**
	 * @param array|IMySqlConnector $config
	 */
	public function initConnector($config): void
	{
		if (is_array($config))
		{
			$sql = \Squid::MySql();
			$sql->config()->setConfig($config);
			
			$config = $sql->getConnector();
		}

		$this->connector = $config;
	}

	public function enqueue(string $queueName, array $payloads): array
	{
		if (!$payloads)
			return [];
		
		$payloadInsert = $this->connector
			->upsert()
			->into(self::PAYLOAD_TABLE)
			->valuesBulk($payloads['payloads'])
			->setDuplicateKeys('Id');
		
		$enqueueInsert = $this->connector
			->upsert()
			->into(self::ENQUEUE_TABLE)
			->valuesBulk($payloads['enqueue'])
			->setDuplicateKeys(['Id', 'DequeueTime']);
		
		$this->connector
			->bulk()
			->add($payloadInsert)
			->add($enqueueInsert)
			->executeAll();

		$mapFunction = function ($o) { return $o['Id']; };
		
		return array_map($mapFunction, $payloads['enqueue']);
	}

	public function dequeue(string $queueName, int $count = 1, int $bufferDelay = 0, int $packageSize = 0): array
	{
		if ($count <= 0)
		{
			return [];
		}
		
		if ($this->needToSetZeroBuffer($queueName, $bufferDelay, $packageSize))
		{
			$bufferDelay = 0;
		}
		
		$ids = $this->getIds($queueName, $count, $bufferDelay);
		
		if (!$ids)
			return [];
		
		$result = $this->connector
			->select()
			->from(self::PAYLOAD_TABLE)
			->byFields(['Id' => $ids, 'QueueName' => $queueName])
			->queryAll(true);
		
		$this->delete($queueName, $ids);
				
		return $result ?: [];
	}

	public function countEnqueued(string $queueName): int
	{
		return $this->connector
			->select()
			->from(self::ENQUEUE_TABLE)
			->byField('QueueName', $queueName)
			->queryCount();
	}

	public function countDelayed(string $queueName): int
	{
		$now = date('Y-m-d H:i:s');
		
		return $this->connector
			->select()
			->from(self::ENQUEUE_TABLE)
			->byField('QueueName', $queueName)
			->where('DequeueTime > ?', $now)
			->queryCount();
	}

	public function clearQueue(string $queueName): void
	{
		$this->connector
			->delete()
			->from(self::ENQUEUE_TABLE)
			->byField('QueueName', $queueName)
			->executeDml();

		$this->connector
			->delete()
			->from(self::PAYLOAD_TABLE)
			->byField('QueueName', $queueName)
			->executeDml();
	}

	public function countNotDelayed(string $queueName, ?int $time = null): int
	{
		$time = $time ?: time();
		$now = date('Y-m-d H:i:s', $time);
		
		return $this->connector
			->select()
			->from(self::ENQUEUE_TABLE)
			->byField('QueueName', $queueName)
			->where('DequeueTime <= ?', $now)
			->queryCount();
	}

	public function countDelayedReadyToDequeue(string $queueName, ?float $delayBuffer = 0.0): int
	{
		$time = time() - (int)floor($delayBuffer);
		
		return $this->countNotDelayed($queueName, $time);
	}

	public function getFirstDelayed(string $queueName): array
	{
		$now = date('Y-m-d H:i:s');

		$firstElement = $this->connector
			->select()
			->from(self::ENQUEUE_TABLE)
			->column('Id', 'DequeueTime')
			->byField('QueueName', $queueName)
			->where('DequeueTime > ?', $now)
			->orderBy('DequeueTime')
			->limit(0, 1)
			->queryRow(true, false);
		
		return $firstElement ? $this->prepareResponse($firstElement) : [];
	}

	public function getDelayedElementByIndex(string $queueName, int $index): array
	{
		$element = $this->connector
			->select()
			->from(self::ENQUEUE_TABLE)
			->column('Id', 'DequeueTime')
			->byField('QueueName', $queueName)
			->orderBy('DequeueTime')
			->limit($index, 1)
			->queryRow(true, false);
		
		return $element ? $this->prepareResponse($element) : [];
	}

	public function flushDelayed(string $queueName): void
	{
		$now = date('Y-m-d H:i:s');
		
		$this->connector
			->update()
			->table(self::ENQUEUE_TABLE)
			->set('DequeueTime', $now)
			->byField('QueueName', $queueName)
			->where('DequeueTime > ?', $now)
			->executeDml();
	}
}