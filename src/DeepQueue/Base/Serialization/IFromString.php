<?php
namespace DeepQueue\Base\Serialization;


interface IFromString 
{
	/**
	 * @return mixed
	 */
	public function deserialize(string $data);
}