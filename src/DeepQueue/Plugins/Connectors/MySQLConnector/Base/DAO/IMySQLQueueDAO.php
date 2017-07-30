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
	public function dequeue(string $queueName, int $count = 1, int $bufferDelay = 0): array;
}