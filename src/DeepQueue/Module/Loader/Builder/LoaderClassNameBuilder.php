<?php
namespace DeepQueue\Module\Loader\Builder;


use DeepQueue\Base\Loader\Decorator\ILoaderDecoratorBuilder;
use DeepQueue\Base\Loader\Decorator\IQueueLoaderDecorator;
use DeepQueue\Exceptions\DecoratorNotExistsException;


class LoaderClassNameBuilder implements ILoaderDecoratorBuilder
{
	private $className;
	
	
	public function __construct($className)
	{
		if (!class_exists($className))
			throw new DecoratorNotExistsException($className);
		
		$this->className = $className;
	}
	
	public function build(): IQueueLoaderDecorator
	{
		return new $this->className;
	}
}