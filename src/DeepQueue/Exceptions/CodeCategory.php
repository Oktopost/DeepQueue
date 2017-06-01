<?php
namespace DeepQueue\Exceptions;


class CodeCategory
{
	use \Objection\TConstsClass;
	
	
	public const GENERIC	= 10000;
	public const VALIDATION	= 20000;
	public const REMOTE		= 30000;
	public const MANAGER	= 40000;
}