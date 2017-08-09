<?php
namespace DeepQueue\Base\Plugins;


interface ICacheableManager extends IManagerPlugin
{
	public function setTTL(int $seconds): void;
	public function flushCache(): void;
}