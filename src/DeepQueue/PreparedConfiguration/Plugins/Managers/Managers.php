<?php
namespace DeepQueue\PreparedConfiguration\Plugins\Managers;


use DeepQueue\Base\Plugins\IManagerPlugin;
use DeepQueue\Plugins\Managers\MySQLManager\MySQLManager;
use DeepQueue\Plugins\Managers\RedisManager\RedisManager;
use DeepQueue\Plugins\Managers\CachedManager\CachedManager;
use DeepQueue\Plugins\Managers\InMemoryManager\InMemoryManager;
use DeepQueue\Plugins\Managers\MemoryCacheManager\MemoryCacheManager;


class Managers
{
	use \Objection\TStaticClass;
	
	
	public static function Cached(IManagerPlugin $main, IManagerPlugin $cache): IManagerPlugin
	{
		return new CachedManager($main, $cache);
	}
	
	public static function InMemory(): IManagerPlugin
	{
		return new InMemoryManager();
	}
	
	public static function MySQL($config): IManagerPlugin
	{
		return new MySQLManager($config);
	}
	
	public static function Redis(array $config): IManagerPlugin
	{
		return new RedisManager($config);
	}
	
	public static function MemoryCached(IManagerPlugin $parent, float $ttl = 1.0): IManagerPlugin
	{
		return new MemoryCacheManager($parent, $ttl);
	}
}