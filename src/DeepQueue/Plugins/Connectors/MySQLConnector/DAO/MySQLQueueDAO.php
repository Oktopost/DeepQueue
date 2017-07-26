<?php
namespace DeepQueue\Plugins\Connectors\MySQLConnector\DAO;


use DeepQueue\Plugins\Connectors\MySQLConnector\Base\DAO\IMySQLQueueDAO;

use Squid\MySql\IMySqlConnector;


/**
 * @autoload
 */
class MySQLQueueDAO implements IMySQLQueueDAO
{
	private const PAYLOAD_TABLE = 'DeepQueuePayload';
	private const ENQUEUE_TABLE = 'DeepQueueEnqueue';
	
	
	/** @var IMySqlConnector */
	private $connector = null;
	
	
	private function getIds(string $queueName, int $count): array 
	{
		$now = date('Y-m-d H:i:s');

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

	public function dequeue(string $queueName, int $count = 1): array
	{
		if ($count <= 0)
		{
			return [];
		}
		
		$ids = $this->getIds($queueName, $count);
		
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
}