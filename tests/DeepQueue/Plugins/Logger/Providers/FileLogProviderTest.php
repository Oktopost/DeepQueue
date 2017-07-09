<?php
namespace DeepQueue\Plugins\Logger\Providers\File;


use DeepQueue\Plugins\Logger\Enum\LogLevel;
use DeepQueue\Plugins\Logger\Log\LogEntry;

use PHPUnit\Framework\TestCase;


class FileLogProviderTest extends TestCase
{
	private const DIR = __DIR__ . '/log';
	
	
	private function getSubject($level = LogLevel::INFO): FileLogProvider
	{
		return new FileLogProvider(self::DIR, $level);
	}
	
	private function getMessage(): string
	{
		$row = null;
		
		foreach(new \DirectoryIterator(self::DIR) as $file)
		{
			if ($file->isFile())
			{
        		$row = file_get_contents(self::DIR . DIRECTORY_SEPARATOR . $file->getFilename());
				break;
			}        
		}
		
		$logRowArray = explode(' ', $row);
		
		return str_replace('"', '', $logRowArray[7]);
	}
	
	protected function setUp()
	{
		if (file_exists(self::DIR))
		{
			array_map('unlink', glob(self::DIR . "/*.*"));
			rmdir(self::DIR);
		}
	}
	
	protected function tearDown()
	{
		if (file_exists(self::DIR))
		{
			array_map('unlink', glob(self::DIR . "/*.*"));
			rmdir(self::DIR);
		}
	}


	public function test_level_gotSettedLevel()
	{
		$provider = $this->getSubject(LogLevel::WARNING);
		
		self::assertEquals(LogLevel::WARNING, $provider->level());
	}
	
	public function test_write_gotDocumentInCouchDB()
	{
		$log = new LogEntry();
		$log->Id = 'provider-test';
		$log->Message = 'test-message';
		$log->Data = new LogEntry();
		$log->Data->Message = 'test';
		
		$this->getSubject()->write($log);

		$logMessage = $this->getMessage();
		
		self::assertEquals($log->Message, $logMessage);
	}
}