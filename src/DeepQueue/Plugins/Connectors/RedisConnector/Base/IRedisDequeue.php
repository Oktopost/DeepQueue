<?php
namespace DeepQueue\Plugins\Connectors\RedisConnector\Base;


interface IRedisDequeue
{
	public function dequeue(int $count = 1, int $waitSeconds): array;
}