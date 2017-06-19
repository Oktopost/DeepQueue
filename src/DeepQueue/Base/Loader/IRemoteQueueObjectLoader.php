<?php
namespace DeepQueue\Base\Loader;


use DeepQueue\Base\IQueueConfig;
use DeepQueue\Base\IQueueObject;


interface IRemoteQueueObjectLoader 
{
	public function create(string $name, ?IQueueConfig $config = null): IQueueObject;
	
	public function load(string $name): ?IQueueObject;
}