<?php
namespace DeepQueue\Base\Queue;


use DeepQueue\Workload;


interface IDequeue
{
	/**
	 * @return mixed|null
	 */
	public function dequeueOnce(?float $waitSeconds = null);

	public function dequeueWorkloadOnce(?float $waitSeconds = null): ?Workload;

	/**
	 * @return mixed[]
	 */
	public function dequeue(int $count, ?float $waitSeconds = null): array;
	
	/**
	 * @return Workload[]
	 */
	public function dequeueWorkload(int $count, ?float $waitSeconds = null): array;
}