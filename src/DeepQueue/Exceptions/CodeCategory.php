<?php
namespace DeepQueue\Exceptions;


use Traitor\TConstsClass;


class CodeCategory
{
	use TConstsClass;
	
	
	public const GENERIC	= 10000;
	public const VALIDATION	= 20000;
	public const REMOTE		= 30000;
	public const MANAGER	= 40000;
}