<?php
namespace DeepQueue\Exceptions;


class InvalidUsageException extends GenericException 
{
	public function __construct($message)
	{
		parent::__construct(2, $message);
	}
}