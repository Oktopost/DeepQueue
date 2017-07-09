<?php
namespace DeepQueue\Utils;


use DeepQueue\Base\Utils\IIdGenerator;


class TimeBasedRandomIdGenerator implements IIdGenerator
{
	public function get(): string
	{
		$str = base_convert(bin2hex(random_bytes(15)), 16, 36);
		$time = base_convert((int)(microtime(true) * 10000), 10, 36);
		$result = $time . $str;
		$len = strlen($result);
		
		if ($len < 32)
		{
			return str_pad($result, 32, '0', STR_PAD_RIGHT);
		}
		else if ($len > 32)
		{
			return substr($time . $str, 0, 32);
		}
		
		return $result;
	}
}