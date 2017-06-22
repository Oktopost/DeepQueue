<?php
namespace DeepQueue\Base\Config;


use DeepQueue\Base\Plugins\IConnectorPlugin;
use DeepQueue\Base\Loader\IQueueLoaderBuilder;
use DeepQueue\Base\Connector\IConnectorProvider;
use DeepQueue\Base\Connector\Decorator\IDecoratorBuilder;


interface IConnectorProviderConfig 
{
	public function setConnector(IConnectorPlugin $connector): IConnectorProviderConfig;
	public function setLoaderBuilder(IQueueLoaderBuilder $loaderBuilder): IConnectorProviderConfig;
	
	/**
	 * @param string|IDecoratorBuilder[] $builders
	 */
	public function addConnectorBuilder(...$builders): void;
	
	public function getConnectorProvider(): IConnectorProvider;
}