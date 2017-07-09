<?php
namespace DeepQueue\Plugins\Logger;


use DeepQueue\Plugins\Logger\Log\LogEntry;
use DeepQueue\Plugins\Logger\Base\ILogger;
use DeepQueue\Plugins\Logger\Base\ILogProvider;
use DeepQueue\Plugins\Logger\Enum\LogLevel;
use DeepQueue\Plugins\Logger\Enum\LogLevelName;
use DeepQueue\Utils\TimeBasedRandomIdGenerator;


class Logger implements ILogger
{
	use \Objection\TSingleton;
	
	
	/** @var ILogProvider[]|array */
	private $providers = [];


	/**
	 * @param mixed $data
	 */
	private function buildRecord(int $level, string $message, $data = null, 
								 ?string $parentId = null, ?string $queueName = null): LogEntry 
	{
		$logEntry = new LogEntry();
		
		$logEntry->Id = (new TimeBasedRandomIdGenerator())->get();
		$logEntry->Level = LogLevelName::MAP[$level];
		$logEntry->Message = $message;
		$logEntry->Data = $data;
		$logEntry->ParentId = $parentId;
		$logEntry->QueueName = $queueName;
			
		return $logEntry;
	}
	
	
	/**
	 * @param mixed $data
	 */
	public function log(int $level, string $message, $data = null, ?string $parentId = null, ?string $queueName = null): void
	{
		$record = $this->buildRecord($level, $message, $data, $parentId, $queueName);
		
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
	public function logException(\Throwable $e, string $message, $data = null, ?string $queueName = null): void
	{
		$this->error("{$message} Got {$e->getMessage()}, Trace: " . PHP_EOL . "{$e->getTraceAsString()}", 
			$data, null, $queueName);
	}

	/**
	 * @param mixed $data
	 */
	public function error(string $message, $data = null, ?string $parentId = null, ?string $queueName = null): void
	{
		$this->log(LogLevel::ERROR, $message, $data, $parentId, $queueName);
	}

	/**
	 * @param mixed $data
	 */
	public function warning(string $message, $data = null, ?string $parentId = null, ?string $queueName = null): void
	{
		$this->log(LogLevel::WARNING, $message, $data, $parentId, $queueName);
	}

	/**
	 * @param mixed $data
	 */
	public function info(string $message, $data = null, ?string $parentId = null, ?string $queueName = null): void
	{
		$this->log(LogLevel::INFO, $message, $data, $parentId, $queueName);
	}

	public function addProvider(ILogProvider $provider): void
	{
		$this->providers[] = $provider;
	}
}