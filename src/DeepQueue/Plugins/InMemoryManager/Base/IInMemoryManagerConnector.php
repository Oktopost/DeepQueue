<?php
namespace DeepQueue\Plugins\InMemoryManager\Base;


use DeepQueue\Base\IQueueObject;


/**
 * @skeleton
 */
interface IInMemoryManagerConnector
{
	public function upsert(IQueueObject $queue): IQueueObject;
	public function load(string $queueName): ?IQueueObject;
	public function loadById(string $queueId): ?IQueueObject;
}