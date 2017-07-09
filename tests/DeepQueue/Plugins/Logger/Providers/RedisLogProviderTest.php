<?php
namespace DeepQueue\Plugins\Logger\Providers\Redis;


use DeepQueue\Utils\RedisConfigParser;
use DeepQueue\Plugins\Logger\Log\LogEntry;
use DeepQueue\Plugins\Logger\Enum\LogLevel;

use PHPUnit\Framework\TestCase;

use Predis\Client;


class RedisLogProviderTest extends TestCase
{
	private function getSubject($level = LogLevel::INFO): RedisLogProvider
	{
		return new RedisLogProvider(['prefix' => 'log-test'], $level);
	}
	
	private function getClient(): Client
	{
		$config = RedisConfigParser::parse(['prefix' => 'log-test']);
		
		return new Client($config->getParameters(), $config->getOptions());
	}
	
	
	protected function setUp()
	{
		$this->getClient()->eval("return redis.call('del', 'defaultKey', unpack(redis.call('keys', ARGV[1])))", 
			0, 'log-test:*');
	}
	
	
	public function test_level_gotSettedLevel()
	{
		$provider = $this->getSubject(LogLevel::WARNING);
		
		self::assertEquals(LogLevel::WARNING, $provider->level());
	}
	
	public function test_write_gotHashMapInRedis()
	{
		$log = new LogEntry();
		$log->Id = 'provider-test';
		$log->Message = 'test-message';
		$log->Data = new LogEntry();
		$log->Data->Message = 'test';
		
		$this->getSubject()->write($log);
		
		$data = $this->getClient()->hgetall('log');
		
		self::assertNotEmpty($data);
		self::assertTrue(isset($data[$log->Id]));
		
		$logData = json_decode($data[$log->Id]);
		
		self::assertEquals($log->Message, $logData->Message);
	}
}