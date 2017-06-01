<?php
namespace DeepQueue\Base\Plugins\ManagerElements;


use DeepQueue\Base\IQueueObject;


interface IQueueDAO
{
	public function load(string $name): IQueueObject;
	public function loadIfExists(string $name): ?IQueueObject;
	public function create(IQueueObject $object): IQueueObject;
	public function update(IQueueObject $object): IQueueObject;
	
	/**
	 * @param string|IQueueObject $object ID or the object itself.
	 */
	public function delete($object): void;
}