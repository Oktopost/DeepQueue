<?php
namespace DeepQueue\Exceptions;


class QueueNotExistsException extends GenericException 
{
	public function __construct($name)
	{
		parent::__construct(1, "Queue $name does not exists!");
	}
}