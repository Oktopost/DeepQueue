<?php
namespace DeepQueue\Plugins\MySQLManager\Base\DAO\Connector;


use Squid\MySql\IMySqlConnector;
use Squid\MySql\Connectors\Object\Generic\IGenericIdConnector;


interface IMySQLManagerConnector extends IGenericIdConnector
{
	public function setMySQL(IMySqlConnector $mysql): IMySQLManagerConnector;
}