<?php
namespace DeepQueue\Utils;


use DeepQueue\Config\RedisConfig;
use DeepQueue\Base\Config\IRedisConfig;
use DeepQueue\Exceptions\InvalidUsageException;


class RedisConfigParser
{
	use \Objection\TStaticClass;
	
	
	private const MAP = 
	[
		'scheme'	=> ['scheme'],
		'host'		=> ['host'],
		'port'		=> ['port'],
		'ssl'		=> ['ssl'],
		'prefix'	=> ['prefix', 'scope']
	];
	
	private const DEFAULTS =
	[
		'scheme'	=> 'tcp',
		'host'		=> '127.0.0.1',
		'port'		=> '6379',
		'ssl'		=> [],
		'prefix'	=> 'deepqueue'	
	];
	
	
	private static function getValue($default, $sectionName, array $config)
	{
		foreach (self::MAP[$sectionName] as $option)
		{
			if (isset($config[$option]))
				return $config[$option];
		}
		
		return $default;
	}
	
	private static function buildObject(array $config): IRedisConfig
	{
		$config = array_change_key_case($config, CASE_LOWER);
		
		$object = new RedisConfig();
		
		$object->Scheme 	= self::getValue(self::DEFAULTS['scheme'], 'scheme', $config);
		$object->Host		= self::getValue(self::DEFAULTS['host'], 'host', $config);
		$object->Port		= self::getValue(self::DEFAULTS['port'], 'port', $config);
		$object->SSL		= self::getValue(self::DEFAULTS['ssl'], 'ssl', $config);
		$object->Prefix		= self::getValue(self::DEFAULTS['prefix'], 'prefix', $config);
		
		return $object;
	}

	/**
	 * @param IRedisConfig|array $config
	 */
	public static function parse($config): IRedisConfig
	{
		if ($config instanceof IRedisConfig)
		{
			return $config;
		}
		
		if (is_array($config))
		{
			return self::buildObject($config);
		}
		
		throw new InvalidUsageException('Redis configuration must be instance of RedisConfig or array');
	}
}