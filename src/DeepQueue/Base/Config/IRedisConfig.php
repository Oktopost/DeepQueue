<?php
namespace DeepQueue\Base\Config;


interface IRedisConfig
{
	public function getParameters(): array;
	public function getOptions(): array;
}