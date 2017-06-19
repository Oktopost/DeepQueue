<?php
namespace DeepQueue\Base\Loader;


use DeepQueue\Base\Loader\Decorator\ILoaderDecoratorBuilder;


interface IQueueLoaderBuilder extends IQueueLoaderProvider
{
	public function setRemoteLoader(IRemoteQueueObjectLoader $loader): void;
	public function addBuilder(ILoaderDecoratorBuilder $builder): void;
}