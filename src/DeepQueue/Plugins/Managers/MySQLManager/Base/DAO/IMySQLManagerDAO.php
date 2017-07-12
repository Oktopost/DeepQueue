<?php
namespace DeepQueue\Plugins\Managers\MySQLManager\Base\DAO;


use DeepQueue\Base\Plugins\ManagerElements\IManagerDAO;

use Squid\MySql\IMySqlConnector;


/**
 * @skeleton
 */
interface IMySQLManagerDAO extends IManagerDAO
{
	/**
	 * @param array|IMySqlConnector $config
	 */
	public function initConnector($config): void;
}