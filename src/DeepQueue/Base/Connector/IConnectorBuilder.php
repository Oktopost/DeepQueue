<?php
namespace DeepQueue\Base\Connector;


use DeepQueue\Base\Connector\Decorator\IDecoratorBuilder;


interface IConnectorBuilder extends IConnectorProvider 
{
	public function setLoader(IQueueLoaderBuilder $loader): void;
	public function addBuilder(IDecoratorBuilder $builder): void;
}