<?php
namespace DeepQueue\Base;


use DeepQueue\Base\Plugins\IRemotePlugin;
use DeepQueue\Base\Plugins\IManagerPlugin;
use DeepQueue\Base\Loader\Decorator\ILoaderDecoratorBuilder;


interface IDeepQueueConfig
{
	/**
	 * @param string[]|array[]|IDeepQueueConfig[] $builder
	 */
	public function addConnectorBuilder(...$builders): IDeepQueueConfig;
	
	/**
	 * @param string|ILoaderDecoratorBuilder[] $builder
	 */
	public function addLoaderBuilder(...$builders): IDeepQueueConfig;
	
	/**
	 * @param int $policy See QueueLoaderPolicy const
	 * @see QueueLoaderPolicy
	 */
	public function setQueueNotExistsPolicy(int $policy): IDeepQueueConfig;
	
	public function setRemotePlugin(IRemotePlugin $plugin): IDeepQueueConfig;
	public function setManagerPlugin(IManagerPlugin $plugin): IDeepQueueConfig;
	
	public function notExistsPolicy(): int;
	public function remote(): IRemotePlugin;
	public function manager(): IManagerPlugin;
}