<?php
namespace DeepQueue\Base\Stream;


use DeepQueue\Workload;


/**
 * @mixin IDequeue
 */
trait TDequeue
{
	/**
	 * @return mixed|null
	 */
	public function dequeueOnce(?float $waitSeconds = null)
	{
		$res = $this->dequeueWorkload(1, $waitSeconds);
		return ($res ? ($res[0])->Payload : null);
	}
	
	public function dequeueWorkloadOnce(?float $waitSeconds = null): ?Workload
	{
		$res = $this->dequeueWorkload(1, $waitSeconds);
		return ($res ? $res[0] : null);
	}
	
	/**
	 * @return mixed[]
	 */
	public function dequeue(int $count, ?float $waitSeconds = null): array
	{
		$res = $this->dequeueWorkload(1, $waitSeconds);
		$payload = [];
		
		if ($res)
		{
			foreach ($res as $workload)
			{
				$payload[] = $workload;
			}
		}
		
		return $payload;
	}
	
	
	/**
	 * @return Workload[]
	 */
	public abstract function dequeueWorkload(int $count, ?float $waitSeconds = null): array;
}