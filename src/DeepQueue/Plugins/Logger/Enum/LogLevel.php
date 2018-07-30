<?php
namespace DeepQueue\Plugins\Logger\Enum;


use Traitor\TConstsClass;


class LogLevel
{
	use TConstsClass;
	
	
	public const ERROR 		= 2000;
	public const WARNING	= 3000;
	public const INFO		= 4000;
}