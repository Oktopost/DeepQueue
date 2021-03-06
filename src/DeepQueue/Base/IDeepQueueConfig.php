<?php
namespace DeepQueue\Base;


use DeepQueue\Base\Config\ILoggerConfig;
use DeepQueue\Base\Config\IPolicyConfig;
use DeepQueue\Base\Config\IManagerConfig;
use DeepQueue\Base\Config\ISerializeConfig;
use DeepQueue\Base\Config\IConnectorConfig;


interface IDeepQueueConfig extends IConnectorConfig, IManagerConfig, IPolicyConfig, ISerializeConfig, ILoggerConfig
{
	
}