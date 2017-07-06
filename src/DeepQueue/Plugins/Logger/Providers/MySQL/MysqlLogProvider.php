<?php
namespace DeepQueue\Plugins\Logger\Providers\MySQL;


use DeepQueue\Plugins\Logger\Log\LogEntry;
use DeepQueue\Plugins\Logger\Enum\LogLevel;
use DeepQueue\Plugins\Logger\Base\ILogProvider;
use DeepQueue\Plugins\Logger\Providers\MySQL\Connector\LogEntryConnector;


class MysqlLogProvider implements ILogProvider
{	
	/** @var LogLevel|int */
	private $logLevel;
	
	
	private $connector;

	
	public function __construct($options = [], $level = LogLevel::ERROR)
	{
		$this->logLevel = $level;

		$this->connector = new LogEntryConnector($options);
	}
	
	
	public function write(LogEntry $record): void
	{
		$this->connector->insert($record);
	}

	public function level(): int
	{
		return $this->logLevel;
	}
}