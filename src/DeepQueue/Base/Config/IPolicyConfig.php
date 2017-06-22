<?php
namespace DeepQueue\Base\Config;


use DeepQueue\Base\IDeepQueueConfig;


interface IPolicyConfig
{
	/**
	 * @param int $policy See QueueLoaderPolicy const
	 * @see QueueLoaderPolicy
	 */
	public function setQueueNotExistsPolicy(int $policy): IDeepQueueConfig;
	
	public function notExistsPolicy(): int;
}