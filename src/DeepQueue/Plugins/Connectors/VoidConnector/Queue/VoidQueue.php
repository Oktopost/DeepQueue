<?php
namespace DeepQueue\Plugins\Connectors\VoidConnector\Queue;


use DeepQueue\Base\IQueueConfig;
use DeepQueue\Base\Queue\Remote\IRemoteQueue;
use DeepQueue\Utils\TimeBasedRandomIdGenerator;


class VoidQueue implements IRemoteQueue
{
	public function dequeueWorkload(int $count = 1, IQueueConfig $config, ?float $waitSeconds = null): array
	{
		return [];
	}

	public function enqueue(array $payload): array
	{
		$ids = [];
		
		foreach ($payload as $item)
		{
			$ids[] = $item->Key ?: (new TimeBasedRandomIdGenerator())->get();
		}
		
		return $ids;
	}
}