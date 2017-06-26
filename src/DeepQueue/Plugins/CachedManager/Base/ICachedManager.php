<?php
namespace DeepQueue\Plugins\CachedManager\Base;


use DeepQueue\Base\Plugins\IManagerPlugin;
use DeepQueue\Base\Plugins\ICacheableManager;


interface ICachedManager extends ICacheableManager
{
	public function __construct(IManagerPlugin $main, IManagerPlugin $cache);
}