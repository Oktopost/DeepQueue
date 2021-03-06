<?php
namespace DeepQueue\PreparedConfiguration;


use Traitor\TStaticClass;

use DeepQueue\DeepQueue;
use DeepQueue\Base\PreparedConfiguration\IPreparedQueue;
use DeepQueue\Base\PreparedConfiguration\Plugins\IPluginConfiguration;
use DeepQueue\PreparedConfiguration\Plugins\MySQLConfiguration;
use DeepQueue\PreparedConfiguration\Plugins\RedisConfiguration;
use DeepQueue\PreparedConfiguration\Plugins\InMemoryConfiguration;
use DeepQueue\PreparedConfiguration\Plugins\RedisMySQLConfiguration;
use DeepQueue\PreparedConfiguration\Plugins\FallbackMySQLConfiguration;
use DeepQueue\PreparedConfiguration\Plugins\FallbackCachedConfiguration;

use Serialization\Base\ISerializer;

use Squid\MySql\IMySqlConnector;


class PreparedQueue implements IPreparedQueue
{
	use TStaticClass;
	
	
	public static function setup(IPluginConfiguration $config): DeepQueue
	{
		$deepQueue = new DeepQueue();
		$deepQueue->config()
			->setManagerPlugin($config->getManager())
			->setConnectorPlugin($config->getConnector())
			->setSerializer($config->getSerializer());

		return $deepQueue;
	}
	
	public static function InMemory(?ISerializer $serializer = null): DeepQueue
	{
		return self::setup(new InMemoryConfiguration($serializer));
	}
	
	/**
	 * @param array $redisConfig
	 * @param array|IMySqlConnector $mysqlConfig
	 * @param null|ISerializer $serializer
	 * @return DeepQueue
	 */
	public static function RedisMySQL(array $redisConfig, $mysqlConfig, ?ISerializer $serializer = null): DeepQueue
	{
		return self::setup(new RedisMySQLConfiguration($redisConfig, $mysqlConfig, $serializer));
	}
	
	/**
	 * @param array $redisConfig
	 * @param array|IMySqlConnector $mysqlConfig
	 * @param null|ISerializer $serializer
	 * @return DeepQueue
	 */
	public static function FallbackCached(array $redisConfig, $mysqlConfig, ?ISerializer $serializer = null): DeepQueue
	{
		return self::setup(new FallbackCachedConfiguration($redisConfig, $mysqlConfig, $serializer));
	}
	
	/**
	 * @param array $redisConfig
	 * @param array|IMySqlConnector $mysqlConfig
	 * @param null|ISerializer $serializer
	 * @return DeepQueue
	 */
	public static function FallbackMySQL(array $redisConfig, $mysqlConfig, ?ISerializer $serializer = null): DeepQueue
	{
		return self::setup(new FallbackMySQLConfiguration($redisConfig, $mysqlConfig, $serializer));
	}
	
	/**
	 * @param array|IMySqlConnector $mysqlConfig
	 * @param null|ISerializer $serializer
	 * @return DeepQueue
	 */
	public static function MySQL($mysqlConfig, ?ISerializer $serializer = null): DeepQueue
	{
		return self::setup(new MySQLConfiguration($mysqlConfig, $serializer));
	}
	
	public static function Redis(array $redisConfig, ?ISerializer $serializer = null): DeepQueue
	{
		return self::setup(new RedisConfiguration($redisConfig, $serializer));
	}
}