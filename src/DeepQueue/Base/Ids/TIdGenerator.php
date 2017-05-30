<?php
namespace DeepQueue\Base\Ids;


/**
 * @mixin IIdGenerator
 */
trait TIdGenerator
{
	public abstract function get(): string;
	
	public function getArray(int $num): array
	{
		$result = [];
		
		for ($i = 0; $i < $num; $i++)
		{
			$result[] = $this->get();
		}
		
		return $result;
	}
}