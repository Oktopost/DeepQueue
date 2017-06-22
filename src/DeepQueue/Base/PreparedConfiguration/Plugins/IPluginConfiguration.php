<?php
namespace DeepQueue\Base\PreparedConfiguration\Plugins;


use DeepQueue\Base\Plugins\IManagerPlugin;
use DeepQueue\Base\Plugins\IConnectorPlugin;


interface IPluginConfiguration 
{
	public function getManager(): IManagerPlugin;
	public function getConnector(): IConnectorPlugin;
	public function getNotExistsPolicy(): int;
}