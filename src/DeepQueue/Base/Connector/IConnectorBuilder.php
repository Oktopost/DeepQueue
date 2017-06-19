<?php
namespace DeepQueue\Base\Connector;


use DeepQueue\Base\Loader\IQueueObjectLoader;
use DeepQueue\Base\Connector\Decorator\IDecoratorBuilder;


interface IConnectorBuilder extends IConnectorProvider 
{
	public function setLoader(IQueueObjectLoader $loader): void;
	public function addBuilder(IDecoratorBuilder $builder): void;
}