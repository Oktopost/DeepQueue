<?php
namespace DeepQueue\Exceptions;


class ValidationErrorCode
{
	use \Objection\TConstsClass;
	
	
	public const KEY_REQUIRED			= 10000;
	public const KEY_FORBIDDEN			= 10001;
	
	public const DELAY_REQUIRED			= 20000;
	public const DELAY_FORBIDDEN		= 20001;	
}