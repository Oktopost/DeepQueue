<?php
namespace DeepQueue\Plugins\MySQLManager\DAO;


use DeepQueue\Scope;
use DeepQueue\Base\IQueueObject;
use DeepQueue\Enums\QueueState;
use DeepQueue\Plugins\MySQLManager\Base\DAO\IMySQLManagerDAO;
use DeepQueue\Plugins\MySQLManager\Base\DAO\Connector\IMySQLManagerConnector;


class MySQLManagerDAO implements IMySQLManagerDAO
{
	/** @var IMySQLManagerConnector */
	private $connector = null;
	
	
	public function initConnector(array $config): void
	{
		$sql = \Squid::MySql();
		$sql->config()->setConfig($config);

		$this->connector = Scope::skeleton(IMySQLManagerConnector::class);
		$this->connector->setMySQL($sql->getConnector());
	}

	public function upsert(IQueueObject $queue): void
	{
		$this->connector->upsert($queue);
	}

	public function load(string $id): ?IQueueObject
	{
		return $this->connector->loadById($id);
	}

	public function loadByName(string $queueName): ?IQueueObject
	{
		return $this->connector
			->selectFirstObjectByFields([
				'Name' 	=> $queueName,
				'State'	=> QueueState::EXISTING
			]);
	}
}