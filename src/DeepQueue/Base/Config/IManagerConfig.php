<?php
namespace DeepQueue\Base\Config;


use DeepQueue\Base\IDeepQueueConfig;
use DeepQueue\Base\Plugins\IManagerPlugin;
use DeepQueue\Base\Loader\IQueueObjectLoader;
use DeepQueue\Base\Utils\IDecoratorBuilder;


interface IManagerConfig
{
	/**
	 * @param string|IDecoratorBuilder[] $builder
	 */
	public function addLoaderBuilder(...$builders): IDeepQueueConfig;
	
	public function getQueueLoader(string $name): IQueueObjectLoader;
	public function setManagerPlugin(IManagerPlugin $plugin): IDeepQueueConfig;
	public function manager(): IManagerPlugin;
}