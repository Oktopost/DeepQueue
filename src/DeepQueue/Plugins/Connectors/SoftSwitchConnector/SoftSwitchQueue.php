<?php
namespace DeepQueue\Plugins\Connectors\SoftSwitchConnector;


use DeepQueue\Payload;
use DeepQueue\Workload;
use DeepQueue\Base\IQueueConfig;
use DeepQueue\Base\Queue\Remote\IRemoteQueue;


class SoftSwitchQueue implements IRemoteQueue
{
	private $from;
	private $to;
	
	
	public function __construct(IRemoteQueue $from, IRemoteQueue $to)
	{
		$this->from = $from;
		$this->to = $to;
	}
	
	
	/**
	 * @param int $count
	 * @param IQueueConfig $config
	 * @param float|null $waitSeconds
	 * @return Workload[]
	 */
	public function dequeueWorkload(int $count = 1, IQueueConfig $config, ?float $waitSeconds = null): array
	{
		$data = $this->from->dequeueWorkload($count, $config, null);
		
		if (!$data)
		{
			$data = $this->to->dequeueWorkload($count, $config, $waitSeconds);
		}
		
		return $data;
	}
	
	/**
	 * @param Payload[] $payload
	 * @return string[]
	 */
	public function enqueue(array $payload): array
	{
		return $this->to->enqueue($payload);
	}
}