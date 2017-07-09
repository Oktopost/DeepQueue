<?php
namespace DeepQueue\Plugins\Logger\Providers\MySQL;


use DeepQueue\Plugins\Logger\Log\LogEntry;
use DeepQueue\Plugins\Logger\Enum\LogLevel;

use lib\MySQLConfig;

use PHPUnit\Framework\TestCase;


class MysqlLogProviderTest extends TestCase
{
	private const TABLE = 'DeepQueueLog';
	
	
	private function getSubject($level = LogLevel::INFO): MysqlLogProvider
	{
		return new MysqlLogProvider(MySQLConfig::get(), $level);
	}

	
	protected function setUp()
	{
		MySQLConfig::clearDB();
		MySQLConfig::initTables();
	}
	
	
	public function test_level_gotSettedLevel()
	{
		$provider = $this->getSubject(LogLevel::WARNING);
		
		self::assertEquals(LogLevel::WARNING, $provider->level());
	}
	
	public function test_write_gotRecordInDB()
	{
		$log = new LogEntry();
		$log->Id = 'provider-test';
		$log->Message = 'test-message';
		$log->Data = new LogEntry();
		$log->Data->Message = 'test';
		
		$this->getSubject()->write($log);
		
		$data = MySQLConfig::connector()
			->select()
			->from(self::TABLE)
			->queryAll(true);
		
		self::assertNotEmpty($data);
		self::assertEquals($log->Id, $data[0]['Id']);
		self::assertEquals($log->Message, $data[0]['Message']);
	}
}