<?php
namespace DeepQueue\Plugins\FallbackConnector\Queue;


use DeepQueue\Payload;
use DeepQueue\Workload;
use DeepQueue\Base\Queue\Remote\IRemoteQueue;
use DeepQueue\Plugins\FallbackConnector\Base\IFallbackQueue;


class FallbackQueue implements IFallbackQueue
{
	/** @var string */
	private $name;
	
	/** @var IRemoteQueue */
	private $main;
	
	/** @var IRemoteQueue */
	private $fallback;
	
	
	private function needToDequeueFromFallback(): bool
	{
		 $rand = (float)rand()/(float)getrandmax();
		 
		 return $rand < 0.2;
	}
	
	
	public function __construct(string $name, IRemoteQueue $main, IRemoteQueue $fallback)
	{
		$this->name = $name;
		
		$this->main = $main;
		$this->fallback = $fallback;
	}

	/**
	 * @return Workload[]|array
	 */
	public function dequeueWorkload(int $count = 1, ?float $waitSeconds = null): array
	{
		try
		{
			$workloads = [];
			
			if ($this->needToDequeueFromFallback())
			{
				$workloads = $this->fallback->dequeueWorkload($count, 0);
			}
			
			if (!$workloads)
			{
				$workloads = $this->main->dequeueWorkload($count, $waitSeconds);
			}
			
			return $workloads;
		}
		catch (\Throwable $e)
		{
			//TODO:: add handler
			return $this->fallback->dequeueWorkload($count, 0);
		}
	}

	/**
	 * @param Payload[] $payload
	 * @return string[]|array IDs for each payload
	 */
	public function enqueue(array $payload): array
	{
		try
		{
			return $this->main->enqueue($payload);
		}
		catch (\Throwable $e)
		{
			//TODO:: add handler
			return $this->fallback->enqueue($payload);
		}
	}
}