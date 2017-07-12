<?php
namespace DeepQueue\Plugins\Connectors\MySQLConnector\Base\DAO;


use Squid\MySql\IMySqlConnector;


/**
 * @skeleton
 */
interface IMySQLQueueDAO
{
	/**
	 * @param array|IMySqlConnector $config
	 */
	public function initConnector($config): void;
	
	public function enqueue(string $queueName, array $payloads): array;
	public function dequeue(string $queueName, int $count = 1): array;
	
	public function countEnqueued(string $queueName): int;
	public function countDelayed(string $queueName): int;
}