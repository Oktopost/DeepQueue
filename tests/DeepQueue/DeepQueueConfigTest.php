<?php
namespace DeepQueue;


use DeepQueue\Base\IDeepQueueConfig;
use DeepQueue\Base\Plugins\IConnectorPlugin;
use DeepQueue\Base\Plugins\IManagerPlugin;
use DeepQueue\Enums\QueueLoaderPolicy;
use DeepQueue\Module\Connector\Decorators\QueueStateDecorator;
use DeepQueue\Module\Loader\Decorators\CachedLoaderDecorator;
use DeepQueue\Plugins\Logger\Base\ILogger;
use DeepQueue\Plugins\Logger\Providers\Redis\RedisLogProvider;

use PHPUnit\Framework\TestCase;

use Serialization\Serializers\PHPSerializer;


class DeepQueueConfigTest extends TestCase
{
	private function getSubject(): IDeepQueueConfig
	{
		return new DeepQueueConfig();
	}
	
	/**
	 * @return \PHPUnit_Framework_MockObject_MockObject|IConnectorPlugin
	 */
	private function mockConnector(): IConnectorPlugin
	{
		return $this->createMock(IConnectorPlugin::class);
	}
	
	/**
	 * @return \PHPUnit_Framework_MockObject_MockObject|IManagerPlugin
	 */
	private function mockManager(): IManagerPlugin
	{
		return$this->createMock(IManagerPlugin::class);
	}
	
	public function test_SetConnectorPlugin_GetIConnectorPlugin()
	{
		$dc = $this->getSubject();
		
		$dc->setConnectorPlugin($this->mockConnector());
		
		self::assertInstanceOf(IConnectorPlugin::class, $dc->connector());
	}
	
	public function test_SetManagerPlugin_GetIManagerPlugin()
	{
		$dc = $this->getSubject();
		
		$dc->setManagerPlugin($this->mockManager());
		
		self::assertInstanceOf(IManagerPlugin::class, $dc->manager());
	}
	
	public function test_SetNotExistQueuePolicy_GetInt()
	{
		$dc = $this->getSubject();
		
		$dc->setQueueNotExistsPolicy(QueueLoaderPolicy::CREATE_NEW);
		
		self::assertEquals(QueueLoaderPolicy::CREATE_NEW, $dc->notExistsPolicy());
	}
	
	public function test_SetSerializer_GetSerializer()
	{
		$dc = $this->getSubject();
		
		$dc->setSerializer(new PHPSerializer());
		
		self::assertInstanceOf(PHPSerializer::class, $dc->serializer());
	}
	
	public function test_AddLogProvider_GetLogger()
	{
		$dc = $this->getSubject();
		
		$dc->addLogProvider(new RedisLogProvider([]));
		
		self::assertInstanceOf(ILogger::class, $dc->logger());
	}
	
	public function test_AddConnectorBuilder_ReturnSelf()
	{
		$dc = $this->getSubject();
		
		$dc->setConnectorPlugin($this->mockConnector());
		$dc->setManagerPlugin($this->mockManager());
		
		self::assertInstanceOf(IDeepQueueConfig::class, 
			$dc->addConnectorBuilder(QueueStateDecorator::class));
	}

	/**
	 * @expectedException \DeepQueue\Exceptions\DecoratorNotExistsException
	 */
	public function test_AddConnectorBuilder_WrongType_ThrowsException()
	{
		$dc = $this->getSubject();
		
		$dc->setConnectorPlugin($this->mockConnector());
		$dc->setManagerPlugin($this->mockManager());
		
		self::assertInstanceOf(IDeepQueueConfig::class, 
			$dc->addConnectorBuilder('wrong'));
	}
	
	public function test_AddLoaderBuilder_ReturnSelf()
	{
		$dc = $this->getSubject();
		
		$dc->setConnectorPlugin($this->mockConnector());
		$dc->setManagerPlugin($this->mockManager());
		
		self::assertInstanceOf(IDeepQueueConfig::class,
			$dc->addLoaderBuilder(CachedLoaderDecorator::class));
	}

	/**
	 * @expectedException \DeepQueue\Exceptions\DecoratorNotExistsException
	 */
	public function test_AddLoaderBuilder_WrongType_ThrowsException()
	{
		$dc = $this->getSubject();
		
		$dc->setConnectorPlugin($this->mockConnector());
		$dc->setManagerPlugin($this->mockManager());
		
		self::assertInstanceOf(IDeepQueueConfig::class,
			$dc->addLoaderBuilder('wrong'));
	}
}