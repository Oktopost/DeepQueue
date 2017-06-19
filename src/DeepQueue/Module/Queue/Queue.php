<?php
namespace DeepQueue\Module\Queue;


use DeepQueue\Payload;
use DeepQueue\Workload;
use DeepQueue\Base\Queue\IQueue;
use DeepQueue\Base\Queue\Remote;
use DeepQueue\Exceptions\UnexpectedDeepQueueException;


class Queue implements IQueue
{
	/** @var Remote\IRemoteDequeue */
	private $remoteDequeue;
	
	/** @var Remote\IRemoteEnqueue */
	private $remoteEnqueue;
	
	
	/**
	 * @param Remote\IRemoteDequeue|Remote\IRemoteQueue $queue
	 * @param Remote\IRemoteEnqueue|null $enqueue
	 */
	public function __construct($queue, Remote\IRemoteEnqueue $enqueue = null)
	{
		if (is_null($enqueue) && $queue instanceof Remote\IRemoteQueue)
		{
			$enqueue = $queue;
		}
		else if (is_null($enqueue) || !($queue instanceof Remote\IRemoteDequeue))
		{
			throw new UnexpectedDeepQueueException('Incorrect parameters passed to Queue constructor!');
		}
		
		$this->remoteDequeue = $queue;
		$this->remoteEnqueue = $enqueue;
	}
	
	
	/**
	 * @return Workload[]
	 */
	public function dequeueWorkload(int $count, ?float $waitSeconds = null): array
	{
		return $this->remoteDequeue->dequeueWorkload($count, $waitSeconds);
	}
	
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
		$res = $this->dequeueWorkload($count, $waitSeconds);
		$payload = [];
		
		if ($res)
		{
			foreach ($res as $workload)
			{
				$payload[] = $workload->Payload;
			}
		}
		
		return $payload;
	}
	
	/**
	 * @param Payload|mixed $payload
	 */
	public function enqueue($payload, ?string $key = null, ?float $delay = null): string
	{
		if (!($payload instanceof Payload))
		{
			$payload = new Payload($payload);
			$payload->Key = $key;
			$payload->Delay = $delay;
		}
		
		$ids = $this->remoteEnqueue->enqueue([$payload]);
		
		return $ids[0];
	}

	/**
	 * @param Payload[]|mixed[] $payloads
	 * @return string[]
	 */
	public function enqueueAll(array $payloads, ?float $delay = null): array
	{
		$hasDelay = !is_null($delay);
		
		foreach ($payloads as $key => $payload)
		{
			if (!($payload instanceof Payload))
			{
				$payload = new Payload($payload);
				$payloads[$key] = $payload;
			}
			
			if ($hasDelay)
			{
				$payload->Delay = $delay;
			}
		}
		
		return $this->remoteEnqueue->enqueue($payloads);
	}
}