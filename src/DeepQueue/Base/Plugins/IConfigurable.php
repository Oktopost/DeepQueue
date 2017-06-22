<?php
namespace DeepQueue\Base\Plugins;


use DeepQueue\Base\IDeepQueueConfig;


interface IConfigurable
{
	public function setConfig(IDeepQueueConfig $config): void;
}