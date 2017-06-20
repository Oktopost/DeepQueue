<?php
namespace DeepQueue\Base\Loader;


use DeepQueue\Base\Loader\Decorator\ILoaderDecoratorBuilder;
use DeepQueue\Base\Loader\Remote\IRemoteQueueObjectLoader;


/**
 * @skeleton
 */
interface IQueueLoaderBuilder extends IQueueLoaderProvider
{
	public function setRemoteLoader(IRemoteQueueObjectLoader $loader): void;
	public function setNewQueuePolicy(int $newQueuePolicy): void;
	public function addBuilder(ILoaderDecoratorBuilder $builder): void;
}