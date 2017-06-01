<?php
namespace DeepQueue\Exceptions;


class GenericException extends DeepQueueException 
{
	public function __construct($code, $message)
	{
		parent::__construct($message, CodeCategory::GENERIC | $code);
	}
}