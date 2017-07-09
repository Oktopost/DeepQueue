<?php
namespace DeepQueue\Base\Loader;


use DeepQueue\Base\Utils\IDecoratorBuilder;
use DeepQueue\Base\Loader\Remote\IRemoteQueueObjectLoader;


/**
 * @skeleton
 */
interface IQueueLoaderBuilder extends IQueueLoaderProvider
{
	public function setRemoteLoader(IRemoteQueueObjectLoader $loader): void;
	public function setNewQueuePolicy(int $newQueuePolicy): void;
	public function addBuilder(IDecoratorBuilder $builder): void;
}