<?php
namespace DeepQueue\Plugins\MySQLConnector\DAO;


use DeepQueue\Plugins\MySQLConnector\Base\DAO\IMySQLQueueDAO;

use Squid\MySql\IMySqlConnector;


class MySQLQueueDAO implements IMySQLQueueDAO
{
	private const PAYLOAD_TABLE = 'DeepQueuePayload';
	private const ENQUEUE_TABLE = 'DeepQueueEnqueue';
	
	
	/** @var IMySqlConnector */
	private $connector = null;
	
	
	public function initConnector(array $config): void
	{
		$sql = \Squid::MySql();
		$sql->config()->setConfig($config);
		
		$this->connector = $sql->getConnector();
	}

	public function enqueue(string $queueName, array $payloads): array
	{
		// TODO: Implement enqueue() method.
	}

	public function dequeue(string $queueName, int $count = 1): array
	{
		// TODO: Implement dequeue() method.
	}

	public function delete(string $queueName, string $payloadId): bool
	{
		$this->connector
			->delete()
			->from(self::ENQUEUE_TABLE)
			->byFields(['Id' => $payloadId, 'QueueName' => $queueName]);
		
		$this->connector
			->delete()
			->from(self::PAYLOAD_TABLE)
			->byFields(['Id' => $payloadId, 'QueueName' => $queueName]);
		
		return true;
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
		return $this->connector
			->select()
			->from(self::ENQUEUE_TABLE)
			->byField('QueueName', $queueName)
			->where('DequeueTime <= ?', time())
			->queryCount();
	}
}