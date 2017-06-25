<?php
namespace DeepQueue\Base\Plugins;


use DeepQueue\Base\IQueueObject;
use DeepQueue\Base\Loader\Remote\IRemoteQueueObjectLoader;


interface IManagerPlugin extends IRemoteQueueObjectLoader, IConfigurable
{
	public function create(IQueueObject $object): IQueueObject;
	public function update(IQueueObject $object): IQueueObject;
	
	/**
	 * @param string|IQueueObject $object Id or the object itself.
	 */
	public function delete($object): void;
}