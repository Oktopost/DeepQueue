<?php
namespace DeepQueue\Plugins\Managers\RedisManager\Base;


use DeepQueue\Base\Config\IRedisConfig;
use DeepQueue\Base\IQueueObject;


/**
 * @skeleton
 */
interface IRedisManagerDAO
{
	public function initClient(IRedisConfig $config);
	public function upsert(IQueueObject $queue): IQueueObject;
	public function load(string $queueName): ?IQueueObject;
	public function delete(string $queueId): void;
	
	public function setTTL(int $seconds): void;
}