<?php
namespace DeepQueue\Plugins\Connectors\RedisConnector\Helper;


use Traitor\TStaticClass;


class RedisNameBuilder
{
	use TStaticClass;
	
	
	private const ZEROKEY = '000-000-000';
	
	private const NOW_SUFFIX = 'now';
	private const DELAYED_SUFFIX = 'delayed';
	private const PAYLOADS_SUFFIX = 'payloads';

	
	public static function getNowKey(string $queueName): string
	{
		return "{$queueName}:" . self::NOW_SUFFIX;
	}
	
	public static function getPayloadsKey(string $queueName): string 
	{
		return "{$queueName}:" . self::PAYLOADS_SUFFIX;
	}
	
	public static function getDelayedKey(string $queueName): string
	{
		return "{$queueName}:" . self::DELAYED_SUFFIX;
	}
	
	public static function getZeroKey(): string
	{
		return self::ZEROKEY;
	}
}