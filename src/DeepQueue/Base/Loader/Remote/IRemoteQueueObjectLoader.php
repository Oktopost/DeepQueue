<?php
namespace DeepQueue\Base\Loader\Remote;


use DeepQueue\Base\IQueueConfig;
use DeepQueue\Base\IQueueObject;


interface IRemoteQueueObjectLoader 
{
	public function load(string $name, bool $canCreate = false): ?IQueueObject;
}