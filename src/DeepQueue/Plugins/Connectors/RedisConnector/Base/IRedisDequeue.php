<?php
namespace DeepQueue\Plugins\Connectors\RedisConnector\Base;


interface IRedisDequeue
{
	public function dequeue(int $count = 1, int $waitSeconds, float $bufferDelay = 0.0): array;
}