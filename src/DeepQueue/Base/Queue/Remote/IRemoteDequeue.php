<?php
namespace DeepQueue\Base\Queue\Remote;


use DeepQueue\Workload;


interface IRemoteDequeue
{
	/**
	 * @return Workload[]
	 */
	public function dequeueWorkload(int $count = 1, ?float $waitSeconds = null): array;
}