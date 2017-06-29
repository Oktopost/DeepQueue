<?php
namespace DeepQueue\Base\Config;


/**
 * @property string Scheme
 * @property string Host
 * @property string Port
 * @property array	SSL
 * @property string Prefix
 */
interface IRedisConfig
{
	public function getParameters(): array;
	public function getOptions(): array;
}