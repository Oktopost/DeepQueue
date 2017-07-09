<?php
namespace DeepQueue\Base\Loader\Remote;


use DeepQueue\Base\IQueueObject;


interface IRemoteQueueObjectLoader 
{
	public function load(string $name, bool $canCreate = false): ?IQueueObject;
	public function loadById(string $id): ?IQueueObject;

	/**
	 * @return IQueueObject[]|array
	 */
	public function loadAll(): array;
}