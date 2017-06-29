<?php
namespace DeepQueue\Utils;


use DeepQueue\Config\RedisConfig;
use DeepQueue\Base\Config\IRedisConfig;

use PHPUnit\Framework\TestCase;


class RedisConfigParserTest extends TestCase
{
	public function test_ParseArray_GetIRedisConfig()
	{
		$config = [
			'prefix' 	=> 'testprefix',
			'scheme'	=> 'testscheme',
			'host'		=> 'testhost',
			'port'		=> '777',
			'ssl'		=> []
		];
		
		/** @var IRedisConfig $configObject */
		$configObject = RedisConfigParser::parse($config);
		
		self::assertInstanceOf(IRedisConfig::class, $configObject);
		
		self::assertEquals($config['prefix'], $configObject->Prefix);
		self::assertEquals($config['scheme'], $configObject->Scheme);
		self::assertEquals($config['host'], $configObject->Host);
		self::assertEquals($config['port'], $configObject->Port);
		self::assertEquals($config['ssl'], $configObject->SSL);
	}
	
	public function test_PassIRedisConfig_GetIRedisConfig()
	{
		$config  = new RedisConfig();
		
		$configObject = RedisConfigParser::parse($config);
		
		self::assertInstanceOf(IRedisConfig::class, $configObject);
	}
	
	/**
	 * @expectedException \DeepQueue\Exceptions\InvalidUsageException
	 */
	public function test_PassWrongParam_GetException()
	{
		$config = RedisConfigParser::parse('wrong');
		
		self::assertInstanceOf(IRedisConfig::class, $config);
	}
}