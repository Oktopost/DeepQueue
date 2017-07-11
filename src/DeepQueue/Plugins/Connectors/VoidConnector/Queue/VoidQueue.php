<?php
namespace DeepQueue\Plugins\Connectors\VoidConnector\Queue;


use DeepQueue\Base\Queue\Remote\IRemoteQueue;


class VoidQueue implements IRemoteQueue
{
	public function dequeueWorkload(int $count = 1, ?float $waitSeconds = null): array
	{
		return [];
	}

	public function enqueue(array $payload): array
	{
		return [];
	}
}