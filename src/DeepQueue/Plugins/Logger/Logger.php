<?php
namespace DeepQueue\Plugins\Logger;


use DeepQueue\Plugins\Logger\Log\LogEntry;
use DeepQueue\Plugins\Logger\Base\ILogger;
use DeepQueue\Plugins\Logger\Base\ILogProvider;
use DeepQueue\Plugins\Logger\Enum\LogLevel;
use DeepQueue\Plugins\Logger\Enum\LogLevelName;
use DeepQueue\Module\Ids\TimeBasedRandomGenerator;

use Objection\LiteObject;


/**
 * @unique
 * @autoload
 */
class Logger implements ILogger
{
	use \Objection\TSingleton;
	
	
	/** @var ILogProvider[]|array */
	private $providers = [];


	/**
	 * @param mixed $data
	 */
	private function buildRecord(int $level, string $message, $data = null, ?string $parentId = null): LogEntry 
	{
		$logEntry = new LogEntry();
		
		$logEntry->Id = (new TimeBasedRandomGenerator())->get();
		$logEntry->Level = LogLevelName::MAP[$level];
		$logEntry->Message = $message;
		$logEntry->Data = $data;
		$logEntry->ParentId = $parentId;
			
		return $logEntry;
	}
	
	
	/**
	 * @param mixed $data
	 */
	public function log(int $level, string $message, $data = null, ?string $parentId = null): void
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

	/**
	 * @param mixed $data
	 */
	public function error(string $message, $data = null, ?string $parentId = null): void
	{
		$this->log(LogLevel::ERROR, $message, $data, $parentId);
	}

	/**
	 * @param mixed $data
	 */
	public function warning(string $message, $data = null, ?string $parentId = null): void
	{
		$this->log(LogLevel::WARNING, $message, $data, $parentId);
	}

	/**
	 * @param mixed $data
	 */
	public function info(string $message, $data = null, ?string $parentId = null): void
	{
		$this->log(LogLevel::INFO, $message, $data, $parentId);
	}

	public function addProvider(ILogProvider $provider): void
	{
		$this->providers[] = $provider;
	}
}