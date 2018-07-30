<?php
namespace lib;


use Squid\MySql;
use Traitor\TStaticClass;


class MySQLConfig
{
	use TStaticClass;
	
	
	private const TABLES = [
		'DeepQueueObject',
		'DeepQueueEnqueue',
		'DeepQueuePayload',
		'DeepQueueLog'
	];
	
	
	/** @var  MySql */
	private static $mysql;
	
	
	public static function get() 
	{
		return [
			'db'		=> '_deepqueue_test_',
			'user'		=> 'root',
			'password'	=> '',
			'host'		=> 'localhost'
		];
	}
	
	public static function initTables()
	{
		$objectTable = file_get_contents(__DIR__ . '/../../sql/DeepQueue.sql');
		$queueTables = file_get_contents(__DIR__ .  '/../../sql/DeepQueueObject.sql');
		$logTables = file_get_contents(__DIR__ . '/../../sql/DeepQueueLog.sql');
		
		self::$mysql->getConnector()->direct($objectTable)->executeDml();
		self::$mysql->getConnector()->direct($queueTables)->executeDml();
		self::$mysql->getConnector()->direct($logTables)->executeDml();
	}

	/**
	 * @return MySql\IMySqlConnector
	 */
	public static function connector()
	{
		return self::$mysql->getConnector();
	}
	public static function clearDB()
	{
		$conn = self::$mysql->getConnector();
		$tables = $conn->db()->listTables();
		$tables = array_filter($tables, function($value) { return in_array($value, self::TABLES); });
		
		foreach ($tables as $table)
		{
			$conn->db()->dropTable($table);
		}
	}


	public static function setup()
	{
		self::$mysql = new MySql();
		self::$mysql->config()->setConfig(self::get());
		self::clearDB();
		
		self::initTables();
	}
}