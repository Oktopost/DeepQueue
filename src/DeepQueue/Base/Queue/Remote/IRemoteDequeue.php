<?php
namespace DeepQueue\Base\Queue\Remote;


use DeepQueue\Workload;
use DeepQueue\Base\IQueueConfig;


interface IRemoteDequeue
{
	/**
	 * @return Workload[]
	 */
	public function dequeueWorkload(int $count = 1, ?float $waitSeconds = null, IQueueConfig $config): array;
}