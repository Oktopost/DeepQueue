<?php
namespace DeepQueue\Base\Plugins\ManagerElements;


use DeepQueue\Base\IQueueObject;


interface IQueueDAO
{
	public function load(string $name): IQueueObject;
	public function loadIfExists(string $name): ?IQueueObject;
	public function create(IQueueObject $object): IQueueObject;
}