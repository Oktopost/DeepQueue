<?php
namespace DeepQueue\Plugins\Managers\MySQLManager\Base\DAO;


use DeepQueue\Base\Plugins\ManagerElements\IManagerDAO;


/**
 * @skeleton
 */
interface IMySQLManagerDAO extends IManagerDAO
{
	public function initConnector(array $config): void;
}