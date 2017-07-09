<?php
namespace DeepQueue\Base\Connector;


use DeepQueue\Base\Utils\IDecoratorBuilder;
use DeepQueue\Base\Loader\IQueueLoaderBuilder;
use DeepQueue\Base\Connector\Remote\IRemoteQueueProvider;


/**
 * @skeleton
 */
interface IConnectorBuilder extends IConnectorProvider 
{
	public function setLoaderBuilder(IQueueLoaderBuilder $loaderBuilder): void;
	public function setRemoteProvider(IRemoteQueueProvider $plugin): void;
	public function addBuilder(IDecoratorBuilder $builder): void;
}