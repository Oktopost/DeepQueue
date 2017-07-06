<?php
namespace DeepQueue\Plugins\Managers\InMemoryManager\Base;


use DeepQueue\Base\IQueueObject;


/**
 * @skeleton
 */
interface IInMemoryManagerDAO
{
	public function upsert(IQueueObject $queue): IQueueObject;
	public function load(string $queueName): ?IQueueObject;
	public function loadById(string $queueId): ?IQueueObject;
}