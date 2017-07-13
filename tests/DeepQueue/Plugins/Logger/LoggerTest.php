<?php
namespace DeepQueue\Plugins\Logger;


use DeepQueue\Plugins\Logger\Base\ILogger;
use DeepQueue\Plugins\Logger\Enum\LogLevel;
use DeepQueue\Plugins\Logger\Enum\LogLevelName;
use DeepQueue\Scope;
use lib\TestLogProvider;
use PHPUnit\Framework\TestCase;


class LoggerTest extends TestCase
{
	/** @var TestLogProvider|null  */
	private $dummyLogProvider = null;
	
	
	private function getSubject(): ILogger
	{
		$this->dummyLogProvider = new TestLogProvider();
		
		$logger = Scope::skeleton(ILogger::class);
		$logger->addProvider($this->dummyLogProvider);
		
		return $logger;
	}
	
	
	public function test_info_loggedInfoRecord()
	{
		$this->getSubject()->info('info-test');
		
		self::assertEquals('info-test', $this->dummyLogProvider->logEntry->Message);
		self::assertEquals(LogLevelName::MAP[LogLevel::INFO], $this->dummyLogProvider->logEntry->Level);
	}
	
	public function test_warning_loggedWarningRecord()
	{
		$this->getSubject()->warning('warning-test');
		
		self::assertEquals('warning-test', $this->dummyLogProvider->logEntry->Message);
		self::assertEquals(LogLevelName::MAP[LogLevel::WARNING], $this->dummyLogProvider->logEntry->Level);
	}
	
	public function test_error_loggedErrorRecord()
	{
		$this->getSubject()->error('error-test');
		
		self::assertEquals('error-test', $this->dummyLogProvider->logEntry->Message);
		self::assertEquals(LogLevelName::MAP[LogLevel::ERROR], $this->dummyLogProvider->logEntry->Level);
	}
	
	public function test_logException_loggedErrorRecord()
	{
		$this->getSubject()->logException(new \Exception(), 'exception-test');
		
		self::assertContains('exception-test', $this->dummyLogProvider->logEntry->Message);
		self::assertEquals(LogLevelName::MAP[LogLevel::ERROR], $this->dummyLogProvider->logEntry->Level);
	}
	
	public function test_log_loggedProvidedInfo()
	{
		$this->getSubject()->log(LogLevel::INFO, 'log-message', [1,2,3], 1, 'test');
		
		self::assertEquals('log-message', $this->dummyLogProvider->logEntry->Message);
		self::assertEquals(LogLevelName::MAP[LogLevel::INFO], $this->dummyLogProvider->logEntry->Level);
		self::assertEquals([1,2,3], $this->dummyLogProvider->logEntry->Data);
		self::assertEquals(1, $this->dummyLogProvider->logEntry->ParentId);
		self::assertEquals('test', $this->dummyLogProvider->logEntry->QueueName);
	}
}