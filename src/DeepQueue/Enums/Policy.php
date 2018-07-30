<?php
namespace DeepQueue\Enums;


use Traitor\TEnum;


class Policy
{
	use TEnum;
	
	
	public const ALLOWED	= 'allowed';
	public const REQUIRED	= 'required';
	public const FORBIDDEN	= 'forbidden';
	public const IGNORED	= 'ignored';
}