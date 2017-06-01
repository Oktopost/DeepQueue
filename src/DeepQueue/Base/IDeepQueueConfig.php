<?php
namespace DeepQueue\Base;


use DeepQueue\Base\Plugins\IRemotePlugin;
use DeepQueue\Base\Plugins\IManagerPlugin;


interface IDeepQueueConfig
{
	/**
	 * @param string[]|array[]|IDeepQueueConfig[] $builder
	 */
	public function addConnectorBuilder(...$builders): IDeepQueueConfig;
	
	/**
	 * @param int $policy See QueueLoaderPolicy const
	 * @see QueueLoaderPolicy
	 */
	public function setQueueNotExistsPolicy(int $policy);
	
	public function setRemotePlugin(IRemotePlugin $plugin): IDeepQueueConfig;
	public function setManagerPlugin(IManagerPlugin $plugin): IDeepQueueConfig;
}