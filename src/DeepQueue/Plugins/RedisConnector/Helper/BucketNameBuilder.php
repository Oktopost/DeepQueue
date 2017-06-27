<?php
namespace DeepQueue\Plugins\RedisConnector\Helper;


class BucketNameBuilder
{
	use \Objection\TStaticClass;
	
	
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
	
}