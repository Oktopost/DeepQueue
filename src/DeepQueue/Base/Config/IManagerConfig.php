<?php
namespace DeepQueue\Base\Config;


use DeepQueue\Base\IDeepQueueConfig;
use DeepQueue\Base\Loader\IQueueObjectLoader;
use DeepQueue\Base\Loader\Decorator\ILoaderDecoratorBuilder;
use DeepQueue\Base\Plugins\IManagerPlugin;


interface IManagerConfig
{
	/**
	 * @param string|ILoaderDecoratorBuilder[] $builder
	 */
	public function addLoaderBuilder(...$builders): IDeepQueueConfig;
	
	public function getQueueLoader(string $name): IQueueObjectLoader;
	public function setManagerPlugin(IManagerPlugin $plugin): IDeepQueueConfig;
	public function manager(): IManagerPlugin;
}