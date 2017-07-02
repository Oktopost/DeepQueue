<?php
namespace DeepQueue\Plugins\Logger\Providers;


use DeepQueue\Plugins\Logger\Base\ILogProvider;
use DeepQueue\Plugins\Logger\Enum\LogLevel;
use DeepQueue\Utils\RedisConfigParser;
use Predis\Client;


class RedisLogProvider implements ILogProvider
{
	private const KEY = 'log';
	
	
	/** @var LogLevel|int */
	private $logLevel;
	
	/** @var Client */
	private $client;
	
	
	public function __construct($options = [], $level = LogLevel::ERROR)
	{
		$this->logLevel = $level;
		
		$redisConfig = RedisConfigParser::parse($options);
		
		$this->client = new Client($redisConfig->getParameters(), $redisConfig->getOptions());
	}


	public function write(array $record): void
	{
		$this->client->hmset(self::KEY, [$record['Id'] => json_encode($record)]);
	}

	public function level(): int
	{
		return $this->logLevel;
	}
}