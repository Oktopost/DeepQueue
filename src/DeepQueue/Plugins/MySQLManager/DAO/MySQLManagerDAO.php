<?php
namespace DeepQueue\Plugins\MySQLManager\DAO;


use DeepQueue\Base\IQueueObject;
use DeepQueue\Plugins\MySQLManager\Base\IMySQLManagerDAO;
use Squid\MySql\IMySqlConnector;


class MySQLManagerDAO implements IMySQLManagerDAO
{
	/**
	 * @var IMySqlConnector
	 */
	private $connector = null;
	
	
	public function setConnector(IMySqlConnector $connector)
	{
		$this->connector = $connector;
	}

	public function upsert(IQueueObject $queue): IQueueObject
	{

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