<?php
namespace DeepQueue\Exceptions;


class CodeCategory
{
	use \Objection\TConstsClass;
	
	
	public const VALIDATION_CATEGORY	= 10000;
	public const REMOTE_CATEGORY		= 20000;
	public const MANAGER_CATEGORY		= 30000;
}