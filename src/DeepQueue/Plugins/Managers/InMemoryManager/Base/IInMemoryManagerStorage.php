<?php
namespace DeepQueue\Plugins\Managers\InMemoryManager\Base;


use DeepQueue\Base\IQueueObject;


/**
 * @skeleton
 */
interface IInMemoryManagerStorage
{
	public function pushQueue(IQueueObject $queue): IQueueObject;
	public function pullQueue(string $name): ?IQueueObject;
	public function pullQueueById(string $id): ?IQueueObject;
}