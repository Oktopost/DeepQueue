<?php
namespace DeepQueue\Plugins\Managers\MySQLManager\DAO\Connector;


use DeepQueue\Manager\QueueConfig;
use DeepQueue\Manager\QueueObject;
use DeepQueue\Plugins\Managers\MySQLManager\Base\DAO\Connector\IMySQLManagerConnector;

use Objection\Mappers;

use Squid\MySql\IMySqlConnector;
use Squid\MySql\Impl\Connectors\Object\Generic\GenericIdConnector;


class MySQLManagerConnector extends GenericIdConnector implements IMySQLManagerConnector
{
	private const TABLE = 'DeepQueueObject';
	
	
	public function __construct()
	{
		parent::__construct();
		
		$mapper = Mappers::simple();
		
		$toObject = function ($o) { return Mappers::simple()->getObject($o, QueueConfig::class); };
		$fromObject = function ($o) { return Mappers::simple()->getJson($o); };
		
		$mapper->setDefaultClassName(QueueObject::class)
			->values()
				->callback('Config', $toObject, $fromObject);
		
		$this
			->setTable(self::TABLE)
			->setIdKey('Id')
			->setObjectMap($mapper);
	}


	public function setMySQL(IMySqlConnector $mysql): IMySQLManagerConnector
	{
		$this->setConnector($mysql);
		return $this;
	}
}