<?php
namespace DeepQueue\Utils;


use Traitor\TStaticClass;


class TimeGenerator
{
	use TStaticClass;
	
	
	public static function getMs(float $offset = 0.0): int
	{
		return round((microtime(true) + $offset) * 1000);
	}
}