<?php
namespace DeepQueue\Module\Connector\Builder;


use DeepQueue\Base\Connector\Decorator\IDecoratorBuilder;
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
	
	public function build(): IRemoteQueueDecorator
	{
		return new $this->className;
	}
}