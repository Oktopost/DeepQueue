<?php
namespace DeepQueue\Exceptions;


class UnexpectedDeepQueueException extends DeepQueueException 
{
	public const UNEXPECTED_ERROR_CODE  = -1;
	
	
	public function __construct($message, \Exception $previous = null)
	{
		parent::__construct($message, self::UNEXPECTED_ERROR_CODE, $previous);
	}
}