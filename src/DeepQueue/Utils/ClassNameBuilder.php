<?php
namespace DeepQueue\Utils;


use DeepQueue\Base\Utils\IDecoratorBuilder;
use DeepQueue\Base\Loader\Decorator\IQueueLoaderDecorator;
use DeepQueue\Base\Connector\Decorator\IRemoteQueueDecorator;
use DeepQueue\Exceptions\DecoratorNotExistsException;


class ClassNameBuilder implements IDecoratorBuilder
{
	private $className;
	
	
	public function __construct($className)
	{
		if (!class_exists($className))
			throw new DecoratorNotExistsException($className);
		
		$this->className = $className;
	}
	
	
	public function buildForConnector(): IRemoteQueueDecorator
	{
		return new $this->className;
	}

	public function buildForLoader(): IQueueLoaderDecorator
	{
		return new $this->className;
	}
}