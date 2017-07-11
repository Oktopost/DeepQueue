<?php
namespace DeepQueue\Plugins\Logger\Providers\Redis;


use DeepQueue\Utils\RedisConfigParser;
use DeepQueue\Plugins\Logger\Log\LogEntry;
use DeepQueue\Plugins\Logger\Enum\LogLevel;
use DeepQueue\Plugins\Logger\Base\ILogProvider;

use Objection\LiteObject;

use Predis\Client;


class RedisLogProvider implements ILogProvider
{
	private const KEY = 'log';
	
	
	/** @var LogLevel|int */
	private $logLevel;
	
	/** @var Client */
	private $client;


	/**
	 * @param mixed $data
	 * @return mixed
	 */
	private function formatData($data)
	{
		if ($data instanceof LiteObject)
		{
			$data = $data->toArray();
		}

		return $data;
	}
	
	
	public function __construct($options = [], $level = LogLevel::ERROR)
	{
		$this->logLevel = $level;
		
		$redisConfig = RedisConfigParser::parse($options);
		
		$this->client = new Client($redisConfig->getParameters(), $redisConfig->getOptions());
	}


	public function write(LogEntry $record): void
	{
		$record = $record->toArray();
		$record['Data'] = $this->formatData($record['Data']);
		
		$this->client->hmset(self::KEY, [$record['Id'] => json_encode($record)]);
	}

	public function level(): int
	{
		return $this->logLevel;
	}
}