<?php
namespace DeepQueue\Exceptions;


class DeepQueueException extends \Exception 
{
	public function __construct($message, $code, \Exception $previous = null)
	{
		parent::__construct($message, $code, $previous);
	}
}