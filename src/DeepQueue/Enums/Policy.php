<?php
namespace DeepQueue\Enums;


class Policy
{
	use \Objection\TEnum;
	
	
	public const ALLOWED	= 'allowed';
	public const REQUIRED	= 'required';
	public const FORBIDDEN	= 'forbidden';
	public const IGNORED	= 'ignored';
}