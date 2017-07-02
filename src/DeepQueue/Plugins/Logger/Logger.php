<?php
namespace DeepQueue\Plugins\Logger;


use DeepQueue\Module\Ids\TimeBasedRandomGenerator;
use DeepQueue\Plugins\Logger\Log\LogEntry;
use DeepQueue\Plugins\Logger\Base\ILogger;
use DeepQueue\Plugins\Logger\Base\ILogProvider;
use DeepQueue\Plugins\Logger\Enum\LogLevel;
use DeepQueue\Plugins\Logger\Enum\LogLevelName;


/**
 * @unique
 * @autoload
 */
class Logger implements ILogger
{
	use \Objection\TSingleton;
	
	
	/** @var ILogProvider[]|array */
	private $providers = [];
	
	
	private function buildRecord(int $level, string $message, $data, $parentId): array 
	{
		$logEntry = new LogEntry();
		
		$logEntry->Id = (new TimeBasedRandomGenerator())->get();
		$logEntry->Level = LogLevelName::MAP[$level];
		$logEntry->Time = time();
		$logEntry->Message = $message;
		$logEntry->Data = $data;
		$logEntry->ParentId = $parentId;
			
		return $logEntry->toArray();
	}
	
	
	public function log(int $level, string $message, $data = null, $parentId = null): void
	{
		$record = $this->buildRecord($level, $message, $data, $parentId);
		
		foreach ($this->providers as $provider)
		{
			if ($provider->level() >= $level)
			{
				$provider->write($record);
			}
		}
	}

	public function error(string $message, $data = null, $parentId = null): void
	{
		$this->log(LogLevel::ERROR, $message, $data, $parentId);
	}

	public function warning(string $message, $data = null, $parentId = null): void
	{
		$this->log(LogLevel::WARNING, $message, $data, $parentId);
	}

	public function info(string $message, $data = null, $parentId = null): void
	{
		$this->log(LogLevel::INFO, $message, $data, $parentId);
	}

	public function addProvider(ILogProvider $provider): void
	{
		$this->providers[] = $provider;
	}
}