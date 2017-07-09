<?php
namespace DeepQueue\Base\Config;


use DeepQueue\Base\IDeepQueueConfig;
use DeepQueue\Base\Plugins\IConnectorPlugin;
use DeepQueue\Base\Connector\IConnectorProvider;
use DeepQueue\Base\Utils\IDecoratorBuilder;


interface IConnectorConfig
{
	/**
	 * @param string[]|array[]|IDecoratorBuilder[] $builder
	 */
	public function addConnectorBuilder(...$builders): IDeepQueueConfig;
	
	public function setConnectorPlugin(IConnectorPlugin $plugin): IDeepQueueConfig;
	public function getConnectorProvider(): IConnectorProvider;
	public function connector(): IConnectorPlugin;
}