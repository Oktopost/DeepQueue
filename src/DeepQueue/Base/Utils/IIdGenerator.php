<?php
namespace DeepQueue\Base\Utils;


interface IIdGenerator
{
	public function get(): string;
}