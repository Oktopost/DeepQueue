<?php
namespace DeepQueue\Exceptions;


class ValidationException extends DeepQueueException 
{
	public function __construct($code, $message)
	{
		parent::__construct($message, CodeCategory::VALIDATION | $code, null);
	}
}