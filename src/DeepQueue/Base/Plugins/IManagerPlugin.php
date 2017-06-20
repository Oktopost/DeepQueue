<?php
namespace DeepQueue\Base\Plugins;


use DeepQueue\Base\IQueueObject;
use DeepQueue\Base\Loader\Remote\IRemoteQueueObjectLoader;


interface IManagerPlugin extends IRemoteQueueObjectLoader
{
	public function update(IQueueObject $object): IQueueObject;
	
	/**
	 * @param string|IQueueObject $object ID or the object itself.
	 */
	public function delete($object): void;
}