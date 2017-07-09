<?php
namespace DeepQueue\Plugins\Managers\MySQLManager\DAO;


use DeepQueue\Base\IQueueObject;
use DeepQueue\Enums\QueueState;
use DeepQueue\Plugins\Managers\MySQLManager\Base\DAO\IMySQLManagerDAO;
use DeepQueue\Plugins\Managers\MySQLManager\Base\DAO\Connector\IMySQLManagerConnector;


class MySQLManagerDAO implements IMySQLManagerDAO
{
	/** @var IMySQLManagerConnector */
	private $connector = null;
	
	
	public function __construct(IMySQLManagerConnector $connector)
	{
		$this->connector = $connector;
	}


	public function initConnector(array $config): void
	{
		$sql = \Squid::MySql();
		$sql->config()->setConfig($config);
		
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
	
	public function loadAll(): array
	{
		return $this->connector
			->selectObjects();
	}
}