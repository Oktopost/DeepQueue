<?php
namespace DeepQueue\Plugins\Logger\Providers\File;


use DeepQueue\Plugins\Logger\Log\LogEntry;
use DeepQueue\Plugins\Logger\Enum\LogLevel;
use DeepQueue\Plugins\Logger\Base\ILogProvider;


class FileLogProvider implements ILogProvider
{
	private const FILENAME 	= 'deepqueue';
	private const EXT		= '.log';
	
	
	/** @var LogLevel|int */
	private $logLevel;
	
	private $path;
	
	
	private function setLogPath(string $dir)
	{
		$dir = rtrim($dir, DIRECTORY_SEPARATOR);
		
		if (!file_exists($dir))
		{
			mkdir($dir, 0644, true);
		}
		
		$this->path = $dir . DIRECTORY_SEPARATOR . self::FILENAME . '_' . date('Y-m-d') . self::EXT;
	}
	
	private function prepare(LogEntry $record): string 
	{
		$parentId = $record->ParentId ? $record->ParentId : ' ';
		$queueName = $record->QueueName ? $record->QueueName : ' ';
		$messageText = str_replace(array("\r", "\n"), ' ', $record->Message);
		
		$message = "[{$record->Created->format('Y-m-d H:i:s')}] ";
		$message .= strtoupper($record->Level);
		$message .= " <{$queueName}> ";
		$message .= "<{$parentId}> ";
		$message .= "\"{$messageText}\"";

		if ($record->Data)
		{
			$data = json_encode($record->Data);
			
			$message .= " '{$data}'";
		}
		
		$message .= PHP_EOL;
		
		return $message;
	}
	
	
	public function __construct(string $dir, $level = LogLevel::ERROR)
	{
		$this->logLevel = $level;
		$this->setLogPath($dir);
	}


	public function write(LogEntry $record): void
	{
		$message = $this->prepare($record);
		
		$fp = fopen($this->path, 'a');
		
		if (!$fp)
		{
			return;
		}
		
		fwrite($fp, $message);
		fclose($fp);
	}

	public function level(): int
	{
		return $this->logLevel;
	}
}