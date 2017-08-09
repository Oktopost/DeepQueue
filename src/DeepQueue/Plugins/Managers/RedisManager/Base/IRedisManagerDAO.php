<?php
namespace DeepQueue\Plugins\Managers\RedisManager\Base;


use DeepQueue\Base\Config\IRedisConfig;
use DeepQueue\Base\Plugins\ManagerElements\IManagerDAO;


/**
 * @skeleton
 */
interface IRedisManagerDAO extends IManagerDAO
{
	public function initClient(IRedisConfig $config): void;
	public function delete(string $queueId): void;
	public function setTTL(int $seconds): void;
	public function deleteAll(): void;
}