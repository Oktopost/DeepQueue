<?php
namespace DeepQueue\Base\Serialization;


interface IToString
{
	/**
	 * @param mixed $object
	 */
	public function serialize($data): string;
}