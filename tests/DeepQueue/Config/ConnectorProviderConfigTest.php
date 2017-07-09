<?php
namespace DeepQueue\Config;


use DeepQueue\Utils\ClassNameBuilder;
use DeepQueue\Base\Plugins\IConnectorPlugin;
use DeepQueue\Base\Loader\IQueueLoaderBuilder;
use DeepQueue\Base\Connector\IConnectorProvider;
use DeepQueue\Base\Config\IConnectorProviderConfig;
use DeepQueue\Module\Connector\Decorators\QueueStateDecorator;

use PHPUnit\Framework\TestCase;


class ConnectorProviderConfigTest extends TestCase
{
	/**
	 * @return \PHPUnit_Framework_MockObject_MockObject|IConnectorPlugin
	 */
	private function mockConnector(): IConnectorPlugin
	{
		$connector = $this->createMock(IConnectorPlugin::class);
		return $connector;
	}
	
	/**
	 * @return \PHPUnit_Framework_MockObject_MockObject|IQueueLoaderBuilder
	 */
	private function mockLoaderBuilder(): IQueueLoaderBuilder
	{
		$loaderBuilder = $this->createMock(IQueueLoaderBuilder::class);
		return $loaderBuilder;
	}
	
	
	public function test_setConnector_returnSelf()
	{
		$config = new ConnectorProviderConfig();
		
		self::assertInstanceOf(IConnectorProviderConfig::class, 
			$config->setConnector($this->mockConnector()));
	}
	
	public function test_setLoaderBuilder_returnSelf()
	{
		$config = new ConnectorProviderConfig();
		
		self::assertInstanceOf(IConnectorProviderConfig::class, 
			$config->setLoaderBuilder($this->mockLoaderBuilder()));
	}

	/**
	 * @expectedException \DeepQueue\Exceptions\InvalidUsageException
	 */
	public function test_getConnectorProvider_NotSetConnector_ThrowsException()
	{
		$config = new ConnectorProviderConfig();
		
		self::assertInstanceOf(IConnectorProvider::class, $config->getConnectorProvider());
	}
	
	/**
	 * @expectedException \DeepQueue\Exceptions\InvalidUsageException
	 */
	public function test_getConnectorProvider_NotSetLoaderBuilder_ThrowsException()
	{
		$config = new ConnectorProviderConfig();
		
		$config->setConnector($this->mockConnector());
		
		self::assertInstanceOf(IConnectorProvider::class, $config->getConnectorProvider());
	}
	
	public function test_getConnectorProvider_NoProvider_CreateNew()
	{
		$config = new ConnectorProviderConfig();
		
		$config->setConnector($this->mockConnector());
		$config->setLoaderBuilder($this->mockLoaderBuilder());
		
		self::assertInstanceOf(IConnectorProvider::class, $config->getConnectorProvider());
	}
	
	public function test_getConnectorProvider_ProviderExists_ReturnIt()
	{
		$config = new ConnectorProviderConfig();
		
		$config->setConnector($this->mockConnector());
		$config->setLoaderBuilder($this->mockLoaderBuilder());
		
		$config->getConnectorProvider();
		
		self::assertInstanceOf(IConnectorProvider::class, $config->getConnectorProvider());
	}
	
	public function test_addConnectorBuilder_PassString()
	{
		$config = new ConnectorProviderConfig();
		$config->setConnector($this->mockConnector());
		$config->setLoaderBuilder($this->mockLoaderBuilder());
		
		self::assertNotNull($config->addConnectorBuilder(QueueStateDecorator::class));
	}
	
	public function test_addConnectorBuilder_PassDecoratorBuilder()
	{
		$config = new ConnectorProviderConfig();
		$config->setConnector($this->mockConnector());
		$config->setLoaderBuilder($this->mockLoaderBuilder());
		
		self::assertNotNull($config
			->addConnectorBuilder(new ClassNameBuilder(QueueStateDecorator::class)));
	}
	
	public function test_addConnectorBuilder_PassArray()
	{
		$config = new ConnectorProviderConfig();
		$config->setConnector($this->mockConnector());
		$config->setLoaderBuilder($this->mockLoaderBuilder());
		
		self::assertNotNull($config
			->addConnectorBuilder([new ClassNameBuilder(QueueStateDecorator::class), 
				QueueStateDecorator::class]));
	}
	
	/**
	 * @expectedException \DeepQueue\Exceptions\InvalidUsageException
	 */
	public function test_addConnectorBuilder_ThrowException()
	{
		$config = new ConnectorProviderConfig();
		$config->setConnector($this->mockConnector());
		$config->setLoaderBuilder($this->mockLoaderBuilder());
		
		self::assertNotNull($config->addConnectorBuilder(new QueueStateDecorator()));
	}
}