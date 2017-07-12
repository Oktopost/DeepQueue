<?php
namespace DeepQueue\Base\PreparedConfiguration;


use DeepQueue\DeepQueue;
use DeepQueue\Base\PreparedConfiguration\Plugins\IPluginConfiguration;

use Serialization\Base\ISerializer;

use Squid\MySql\IMySqlConnector;


interface IPreparedQueue
{
	public static function setup(IPluginConfiguration $configuration): DeepQueue;
	
	public static function InMemory(?ISerializer $serializer = null): DeepQueue;
	public static function MySQL($mysqlConfig, ?ISerializer $serializer = null): DeepQueue;
	public static function Redis(array $redisConfig, ?ISerializer $serializer = null): DeepQueue;

	/**
	 * @param array|IMySqlConnector $mysqlConfig
	 */
	public static function RedisMySQL(array $redisConfig, $mysqlConfig, ?ISerializer $serializer = null): DeepQueue;
	
	/**
	 * @param array|IMySqlConnector $mysqlConfig
	 */
	public static function FallbackCached(array $redisConfig, $mysqlConfig, ?ISerializer $serializer = null): DeepQueue;
	
	/**
	 * @param array|IMySqlConnector $mysqlConfig
	 */
	public static function FallbackMySQL(array $redisConfig, $mysqlConfig, ?ISerializer $serializer = null): DeepQueue;
}