<?php
namespace DeepQueue\Base\Config;


use DeepQueue\Base\Utils\IDecoratorBuilder;
use DeepQueue\Base\Plugins\IConnectorPlugin;
use DeepQueue\Base\Loader\IQueueLoaderBuilder;
use DeepQueue\Base\Connector\IConnectorProvider;


interface IConnectorProviderConfig 
{
	public function setConnector(IConnectorPlugin $connector): IConnectorProviderConfig;
	public function setLoaderBuilder(IQueueLoaderBuilder $loaderBuilder): IConnectorProviderConfig;
	
	/**
	 * @param string|IDecoratorBuilder[] $builders
	 */
	public function addConnectorBuilder(...$builders): IConnectorProviderConfig;
	
	public function getConnectorProvider(): IConnectorProvider;
}