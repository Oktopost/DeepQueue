<?php
namespace DeepQueue\Base\PreparedConfiguration;


use DeepQueue\DeepQueue;
use DeepQueue\Base\PreparedConfiguration\Plugins\IPluginConfiguration;

use Serialization\Base\ISerializer;


interface IPreparedQueue
{
	public static function setup(IPluginConfiguration $configuration): DeepQueue;
	
	public static function InMemory(?ISerializer $serializer = null): DeepQueue;
	public static function MySQL(array $mysqlConfig, ?ISerializer $serializer = null): DeepQueue;
	public static function Redis(array $redisConfig, ?ISerializer $serializer = null): DeepQueue;
	
	public static function RedisMySQL(array $mysqlConfig, array $redisConfig, ?ISerializer $serializer = null): DeepQueue;
	public static function FallbackCached(array $mysqlConfig, array $redisConfig, ?ISerializer $serializer = null): DeepQueue;
	public static function FallbackMySQL(array $mysqlConfig, array $redisConfig, ?ISerializer $serializer = null): DeepQueue;
}