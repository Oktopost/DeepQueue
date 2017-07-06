<?php
namespace DeepQueue\PreparedConfiguration;


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


class PreparedQueue implements IPreparedQueue
{
	use \Objection\TStaticClass;
	
	
	public static function setup(IPluginConfiguration $config): DeepQueue
	{
		$deepQueue = new DeepQueue();
		$deepQueue->config()
			->setQueueNotExistsPolicy($config->getNotExistsPolicy())
			->setManagerPlugin($config->getManager())
			->setConnectorPlugin($config->getConnector())
			->setSerializer($config->getSerializer());

		return $deepQueue;
	}
	
	public static function InMemory(?ISerializer $serializer = null): DeepQueue
	{
		return self::setup(new InMemoryConfiguration($serializer));
	}
	
	public static function RedisMySQL(array $mysqlConfig, array $redisConfig, ?ISerializer $serializer = null): DeepQueue
	{
		return self::setup(new RedisMySQLConfiguration($mysqlConfig, $redisConfig, $serializer));
	}
	
	public static function FallbackCached(array $mysqlConfig, array $redisConfig, ?ISerializer $serializer = null): DeepQueue
	{
		return self::setup(new FallbackCachedConfiguration($mysqlConfig, $redisConfig, $serializer));
	}
	
	public static function FallbackMySQL(array $mysqlConfig, array $redisConfig, ?ISerializer $serializer = null): DeepQueue
	{
		return self::setup(new FallbackMySQLConfiguration($mysqlConfig, $redisConfig, $serializer));
	} 
	
	public static function MySQL(array $mysqlConfig, ?ISerializer $serializer = null): DeepQueue
	{
		return self::setup(new MySQLConfiguration($mysqlConfig, $serializer));
	}
	
	public static function Redis(array $redisConfig, ?ISerializer $serializer = null): DeepQueue
	{
		return self::setup(new RedisConfiguration($redisConfig, $serializer));
	}
}