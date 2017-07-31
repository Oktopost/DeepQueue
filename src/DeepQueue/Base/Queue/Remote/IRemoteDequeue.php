<?php
namespace DeepQueue\Base\Queue\Remote;


use DeepQueue\Workload;
use DeepQueue\Base\IQueueConfig;


interface IRemoteDequeue
{
	/**
	 * @return Workload[]
	 */
	public function dequeueWorkload(int $count = 1, IQueueConfig $config, ?float $waitSeconds = null): array;
}