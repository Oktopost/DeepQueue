<?php
namespace DeepQueue\Base\Queue;


use DeepQueue\Workload;


interface IDequeue
{
	/**
	 * @return mixed|null
	 */
	public function dequeueOnce(?float $waitSeconds = null, float $bufferDelay = 0.0);

	public function dequeueWorkloadOnce(?float $waitSeconds = null, float $bufferDelay = 0.0): ?Workload;

	/**
	 * @return mixed[]
	 */
	public function dequeue(int $count, ?float $waitSeconds = null, float $bufferDelay = 0.0): array;
	
	/**
	 * @return Workload[]
	 */
	public function dequeueWorkload(int $count, ?float $waitSeconds = null, float $bufferDelay = 0.0): array;
}