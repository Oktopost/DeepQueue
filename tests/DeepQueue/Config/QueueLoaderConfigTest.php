<?php
namespace DeepQueue\Config;


use DeepQueue\Enums\QueueLoaderPolicy;
use DeepQueue\Base\Plugins\IManagerPlugin;
use DeepQueue\Base\Config\IQueueLoaderConfig;
use DeepQueue\Base\Loader\IQueueObjectLoader;
use DeepQueue\Base\Loader\IQueueLoaderBuilder;
use DeepQueue\Module\Loader\Decorators\CachedLoaderDecorator;
use DeepQueue\Utils\ClassNameBuilder;

use PHPUnit\Framework\TestCase;


class QueueLoaderConfigTest extends TestCase
{
	/**
	 * @return \PHPUnit_Framework_MockObject_MockObject|IManagerPlugin
	 */
	private function mockManager(): IManagerPlugin
	{
		$connector = $this->createMock(IManagerPlugin::class);
		return $connector;
	}
	
	
	public function test_setManager_returnSelf()
	{
		$config = new QueueLoaderConfig();
		
		self::assertInstanceOf(IQueueLoaderConfig::class, 
			$config->setManager($this->mockManager()));
	}
	
	public function test_setQueueNotExistPolicy_returnSelf()
	{
		$config = new QueueLoaderConfig();
		
		self::assertInstanceOf(IQueueLoaderConfig::class, 
			$config->setQueueNotExistsPolicy(QueueLoaderPolicy::CREATE_NEW));
	}

	/**
	 * @expectedException \DeepQueue\Exceptions\InvalidUsageException
	 */
	public function test_getLoaderBuilder_NotSetManager_ThrowsException()
	{
		$config = new QueueLoaderConfig();
		
		self::assertInstanceOf(IQueueLoaderConfig::class, $config->getLoaderBuilder());
	}
	
	/**
	 * @expectedException \DeepQueue\Exceptions\InvalidUsageException
	 */
	public function test_getQueueLoader_NotSetManager_ThrowsException()
	{
		$config = new QueueLoaderConfig();
		
		self::assertInstanceOf(IQueueLoaderConfig::class, $config->getQueueLoader('test'));
	}
	
	public function test_getLoaderBuilder_NoBuilder_CreateNew()
	{
		$config = new QueueLoaderConfig();
		
		$config->setManager($this->mockManager());
		
		self::assertInstanceOf(IQueueLoaderBuilder::class, $config->getLoaderBuilder());
	}
	
	public function test_getLoaderBuilder_BuilderExists_ReturnIt()
	{
		$config = new QueueLoaderConfig();
		
		$config->setManager($this->mockManager());
		
		$builder = $config->getLoaderBuilder();
		
		self::assertInstanceOf(IQueueLoaderBuilder::class, $config->getLoaderBuilder());
	}
	
	public function test_addLoaderBuilder_PassString()
	{
		$config = new QueueLoaderConfig();
		$config->setManager($this->mockManager());
		
		self::assertNotNull($config->addLoaderBuilder(CachedLoaderDecorator::class));
	}
	
	public function test_addConnectorBuilder_PassDecoratorBuilder()
	{
		$config = new QueueLoaderConfig();
		$config->setManager($this->mockManager());
		
		self::assertNotNull($config
			->addLoaderBuilder(new ClassNameBuilder(CachedLoaderDecorator::class)));
	}
	
	public function test_addConnectorBuilder_PassArray()
	{
		$config = new QueueLoaderConfig();
		$config->setManager($this->mockManager());
		
		self::assertNotNull($config
			->addLoaderBuilder([new ClassNameBuilder(CachedLoaderDecorator::class), 
				CachedLoaderDecorator::class]));
	}
	
	/**
	 * @expectedException \DeepQueue\Exceptions\InvalidUsageException
	 */
	public function test_addConnectorBuilder_ThrowException()
	{
		$config = new QueueLoaderConfig();
		$config->setManager($this->mockManager());
		
		self::assertNotNull($config->addLoaderBuilder(new CachedLoaderDecorator()));
	}
	
	public function test_getQueueLoader_NoBuilder_ReturnIQueueObjectLoader()
	{
		$config = new QueueLoaderConfig();
		$config->setManager($this->mockManager());
		
		self::assertInstanceOf(IQueueObjectLoader::class, $config->getQueueLoader('test'));
	}
	
	public function test_getQueueLoader_BuilderExists_ReturnIQueueObjectLoader()
	{
		$config = new QueueLoaderConfig();
		$config->setManager($this->mockManager());
		
		$loader = $config->getQueueLoader('test');
		
		self::assertInstanceOf(IQueueObjectLoader::class, $config->getQueueLoader('test'));
	}
}