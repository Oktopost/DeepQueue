<?php
namespace DeepQueue\PreparedConfiguration\Plugins\Connectors;


use DeepQueue\Base\Plugins\IConnectorPlugin;
use DeepQueue\Plugins\Connectors\RedisConnector\RedisConnector;
use DeepQueue\Plugins\Connectors\MySQLConnector\MySQLConnector;
use DeepQueue\Plugins\Connectors\FallbackConnector\FallbackConnector;
use DeepQueue\Plugins\Connectors\InMemoryConnector\InMemoryConnector;

use Squid\MySql\IMySqlConnector;
use Traitor\TStaticClass;


class Connectors
{
	use TStaticClass;
	
	
	public static function Fallback(IConnectorPlugin $main, IConnectorPlugin $fallback): IConnectorPlugin
	{
		return new FallbackConnector($main, $fallback);
	}
	
	public static function InMemory($emulateErrors = false): IConnectorPlugin
	{
		return new InMemoryConnector($emulateErrors);
	}
	
	/**
	 * @param array|IMySqlConnector $mysqlConfig
	 */
	public static function MySQL($config): IConnectorPlugin
	{
		return new MySQLConnector($config);
	}
	
	public static function Redis(array $config): IConnectorPlugin
	{
		return new RedisConnector($config);
	}
}