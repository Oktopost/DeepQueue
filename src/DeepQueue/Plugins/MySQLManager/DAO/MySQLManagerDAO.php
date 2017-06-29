<?php
namespace DeepQueue\Plugins\MySQLManager\DAO;


use DeepQueue\Base\IQueueObject;
use DeepQueue\Plugins\MySQLManager\Base\IMySQLManagerDAO;


class MySQLManagerDAO implements IMySQLManagerDAO
{
	public function setConfig($config)
	{
		// TODO: Implement setConfig() method.
	}

	public function upsert(IQueueObject $queue): IQueueObject
	{
		// TODO: Implement upsert() method.
	}

	public function loadById(string $id): ?IQueueObject
	{
		// TODO: Implement loadById() method.
	}

	public function loadByName(string $queueName): ?IQueueObject
	{
		// TODO: Implement loadByName() method.
	}
}