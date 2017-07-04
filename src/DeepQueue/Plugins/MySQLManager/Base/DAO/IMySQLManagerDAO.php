<?php
namespace DeepQueue\Plugins\MySQLManager\Base\DAO;


use DeepQueue\Base\IQueueObject;

use Squid\MySql\IMySqlConnector;


/**
 * @skeleton
 */
interface IMySQLManagerDAO
{
	public function initConnector(array $config): void;
	public function upsert(IQueueObject $queue): void;
	public function load(string $id): ?IQueueObject;
	public function loadByName(string $queueName): ?IQueueObject;
}