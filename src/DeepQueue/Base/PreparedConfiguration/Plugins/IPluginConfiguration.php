<?php
namespace DeepQueue\Base\PreparedConfiguration\Plugins;


use DeepQueue\Base\Plugins\IManagerPlugin;
use DeepQueue\Base\Plugins\IRemotePlugin;


interface IPluginConfiguration 
{
	public function getManager(): IManagerPlugin;
	public function getRemote(): IRemotePlugin;
	public function getNotExistsPolicy(): int;
}