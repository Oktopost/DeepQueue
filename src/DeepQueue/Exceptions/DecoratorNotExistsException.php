<?php
namespace DeepQueue\Exceptions;


class DecoratorNotExistsException extends GenericException 
{
	public function __construct($className)
	{
		parent::__construct(2, "Queue decorator $className does not exists!");
	}
}