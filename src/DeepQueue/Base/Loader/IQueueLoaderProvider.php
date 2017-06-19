<?php
namespace DeepQueue\Base\Loader;


interface IQueueLoaderProvider
{
	public function getRemoteLoader(string $name, $newQueuePolicy): IQueueObjectLoader;
}