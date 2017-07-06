<?php
namespace DeepQueue\Plugins\Connectors\FallbackConnector\Base;


use DeepQueue\Base\Plugins\IConnectorPlugin;


interface IFallbackConnector extends IConnectorPlugin
{
	public function __construct(IConnectorPlugin $main, IConnectorPlugin $fallback);
}