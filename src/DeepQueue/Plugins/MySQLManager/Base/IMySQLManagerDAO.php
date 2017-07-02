<?php
namespace DeepQueue\Plugins\MySQLManager\Base;


use DeepQueue\Base\IQueueObject;

use Squid\MySql\IMySqlConnector;


/**
 * @skeleton
 */
interface IMySQLManagerDAO
{
	public function setConnector(IMySqlConnector $connector);
	public function upsert(IQueueObject $queue): IQueueObject;
	public function loadById(string $id): ?IQueueObject;
	public function loadByName(string $queueName): ?IQueueObject;
}