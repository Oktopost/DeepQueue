<?php
namespace DeepQueue\Plugins\Connectors\FallbackConnector\Queue;


use DeepQueue\Base\IQueueConfig;
use DeepQueue\Payload;
use DeepQueue\Workload;
use DeepQueue\Base\Queue\Remote\IRemoteQueue;
use DeepQueue\Plugins\Logger\Base\ILogger;
use DeepQueue\Plugins\Connectors\FallbackConnector\Base\IFallbackQueue;


class FallbackQueue implements IFallbackQueue
{
	/** @var string */
	private $name;
	
	/** @var IRemoteQueue */
	private $main;
	
	/** @var IRemoteQueue */
	private $fallback;
	
	/** @var ILogger */
	private $logger;
	
	
	private function needToDequeueFromFallback(): bool
	{
		 $rand = (float)rand()/(float)getrandmax();
		 
		 return $rand < 0.2;
	}
	
	private function log(\Throwable $e, string $operation, array $data): void
	{
		$message = "Fallback queue error: Failed to {$operation} data for {$this->name} queue.";
		$this->logger->logException($e, $message, $data, $this->name);
	}
	
	
	public function __construct(string $name, IRemoteQueue $main, IRemoteQueue $fallback, ILogger $logger)
	{
		$this->name = $name;
		$this->logger = $logger;
		
		$this->main = $main;
		$this->fallback = $fallback;
	}

	
	/**
	 * @return Workload[]|array
	 */
	public function dequeueWorkload(int $count = 1, IQueueConfig $config, ?float $waitSeconds = null): array
	{
		try
		{
			$workloads = [];
			
			if ($this->needToDequeueFromFallback())
			{
				$workloads = $this->fallback->dequeueWorkload($count, $config);
			}
			
			if (!$workloads)
			{
				$workloads = $this->main->dequeueWorkload($count, $config, $waitSeconds);
			}
			
			return $workloads;
		}
		catch (\Throwable $e)
		{
			$this->log($e, 'dequeueWorkload', [
				'queue'			=> $this->name,
				'count' 		=> $count,
				'waitSeconds'	=> $waitSeconds,
				'delayBuffer'	=> $config->DelayBuffer,
				'packageSize'	=> $config->PackageSize
			]);
			
			return $this->fallback->dequeueWorkload($count, $config);
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
			$this->log($e, 'enqueue', [
				'queue'			=> $this->name,
				'payloadsCount' => sizeof($payload),
			]);
						
			return $this->fallback->enqueue($payload);
		}
	}
}