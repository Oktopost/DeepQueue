<?php
namespace DeepQueue\Base\Plugins;


use DeepQueue\Base\IDeepQueueConfig;


interface IConfigurable
{
	public function setDeepConfig(IDeepQueueConfig $config): void;
}