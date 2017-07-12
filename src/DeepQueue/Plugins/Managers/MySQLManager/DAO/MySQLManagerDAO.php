<?php
namespace DeepQueue\Plugins\Managers\MySQLManager\DAO;


use DeepQueue\Base\IQueueObject;
use DeepQueue\Enums\QueueState;
use DeepQueue\Plugins\Managers\MySQLManager\Base\DAO\IMySQLManagerDAO;
use DeepQueue\Plugins\Managers\MySQLManager\Base\DAO\Connector\IMySQLManagerConnector;

use Squid\MySql\IMySqlConnector;


/**
 * @autoload
 */
class MySQLManagerDAO implements IMySQLManagerDAO
{
	/** @var IMySQLManagerConnector */
	private $connector = null;
	
	
	public function __construct(IMySQLManagerConnector $connector)
	{
		$this->connector = $connector;
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
		
		$this->connector->setMySQL($config);
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