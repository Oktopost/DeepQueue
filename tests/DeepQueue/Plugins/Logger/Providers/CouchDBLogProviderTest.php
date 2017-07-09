<?php
namespace DeepQueue\Plugins\Logger\Providers\CouchDB;


use DeepQueue\Plugins\Logger\Enum\LogLevel;
use DeepQueue\Plugins\Logger\Log\LogEntry;

use PHPOnCouch\CouchClient;

use PHPUnit\Framework\TestCase;


class CouchDBLogProviderTest extends TestCase
{
	private const DATABASE = 'deepqueue_log_test';
	private const DSN = 'http://127.0.0.1:5984';
	
	
	private function getSubject($level = LogLevel::INFO): CouchDBLogProvider
	{
		return new CouchDBLogProvider(['dsn' => self::DSN, 'db' => self::DATABASE], $level);
	}
	
	private function getClient(): CouchClient
	{
		$config = ['dsn' => self::DSN, 'db' => self::DATABASE];
		return new CouchClient($config['dsn'], $config['db']);
	}

	
	protected function setUp()
	{
		$client = $this->getClient();
		$client->deleteDatabase();
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
		
		$data = $this->getClient()
			->include_docs(true)
			->asArray()
			->getAllDocs();

		self::assertNotEmpty($data['rows']);
		self::assertEquals($log->Id, $data['rows'][0]['id']);
		self::assertEquals($log->Message, $data['rows'][0]['doc']['Message']);
	}
}