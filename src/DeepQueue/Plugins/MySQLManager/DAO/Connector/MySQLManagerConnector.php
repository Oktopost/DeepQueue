<?php
namespace DeepQueue\Plugins\MySQLManager\DAO\Connector;


use DeepQueue\Manager\QueueConfig;
use DeepQueue\Manager\QueueObject;
use DeepQueue\Plugins\MySQLManager\Base\DAO\Connector\IMySQLManagerConnector;

use Objection\Mappers;

use Squid\MySql\IMySqlConnector;
use Squid\MySql\Impl\Connectors\Object\Generic\GenericIdConnector;


class MySQLManagerConnector extends GenericIdConnector implements IMySQLManagerConnector
{
	private const TABLE = 'DeepQueueObject';
	
	
	public function __construct($parent = null, $excludeFields = null)
	{
		parent::__construct($parent, $excludeFields);
		
		$mapper = Mappers::simple();
		
		$mapper->setDefaultClassName(QueueObject::class)
			->values()
				->jsonMapper('Config', Mappers::simple(), QueueConfig::class);
		
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