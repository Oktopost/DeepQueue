<?php
namespace DeepQueue\Base\Ids;


interface IIdGenerator
{
	public function get(): string;
	public function getArray(int $num): array;
}