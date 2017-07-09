<?php
namespace lib;


use DeepQueue\Plugins\Logger\Base\ILogProvider;
use DeepQueue\Plugins\Logger\Enum\LogLevel;
use DeepQueue\Plugins\Logger\Log\LogEntry;


class TestLogProvider implements ILogProvider
{
	/** @var LogEntry */
	public $logEntry = null;
	
	
	public function write(LogEntry $record): void
	{
		$this->logEntry = $record;
	}

	public function level(): int
	{
		return LogLevel::INFO;
	}
}