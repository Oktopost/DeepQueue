<?php
namespace DeepQueue\Plugins\Managers\RedisManager\Base;


use DeepQueue\Base\Config\IRedisConfig;
use DeepQueue\Base\IQueueObject;
use DeepQueue\Base\Plugins\ManagerElements\IManagerDAO;


/**
 * @skeleton
 */
interface IRedisManagerDAO extends IManagerDAO
{
	public function initClient(IRedisConfig $config);
	public function delete(string $queueId): void;
	public function setTTL(int $seconds): void;
}