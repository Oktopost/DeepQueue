<?php
namespace DeepQueue\Base\Queue\Remote;


use DeepQueue\Workload;
use DeepQueue\Base\IQueueConfig;


interface IRemoteDequeue
{
	/**
	 * @param int $count
	 * @param IQueueConfig $config
	 * @param float|null $waitSeconds
	 * @return array|Workload[]
	 */
	public function dequeueWorkload(int $count = 1, IQueueConfig $config, ?float $waitSeconds = null): array;
}