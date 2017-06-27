<?php
namespace DeepQueue\Utils;


class TimeGenerator
{
	use \Objection\TStaticClass;
	
	
	public static function getMs(float $offset = 0.0): int
	{
		return round((microtime(true) + $offset) * 1000);
	}
}