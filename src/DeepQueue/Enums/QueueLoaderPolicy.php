<?php
namespace DeepQueue\Enums;


class QueueLoaderPolicy
{
	use \Objection\TEnum;
	
	
	public const CREATE_NEW		= 0;
	public const FORBIDDEN		= 1;
}