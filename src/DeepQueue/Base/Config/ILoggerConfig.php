<?php
namespace DeepQueue\Base\Config;


use DeepQueue\Base\IDeepQueueConfig;
use DeepQueue\Plugins\Logger\Base\ILogger;
use DeepQueue\Plugins\Logger\Base\ILogProvider;


interface ILoggerConfig
{
	public function addLogProvider(ILogProvider $provider): IDeepQueueConfig;
	
	public function logger(): ILogger;
}