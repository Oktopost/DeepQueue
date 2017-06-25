<?php
namespace DeepQueue\Plugins\RedisManager\Base;


use DeepQueue\Base\IQueueObject;


/**
 * @skeleton
 */
interface IRedisManagerConnector
{
	public function upsert(IQueueObject $queue): IQueueObject;
	public function load(string $queueName): ?IQueueObject;
	public function delete(string $queueId): void;
}