<?php
namespace DeepQueue\Enums;


use Traitor\TEnum;


class QueueLoaderPolicy
{
	use TEnum;
	
	
	public const CREATE_NEW		= 0;
	public const FORBIDDEN		= 1;
}