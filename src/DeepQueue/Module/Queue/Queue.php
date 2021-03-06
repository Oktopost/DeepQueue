<?php
namespace DeepQueue\Module\Queue;


use DeepQueue\Base\IQueueConfig;
use DeepQueue\Payload;
use DeepQueue\Workload;
use DeepQueue\Base\Queue\IQueue;
use DeepQueue\Base\Queue\Remote;
use DeepQueue\Plugins\Logger\Base\ILogger;
use DeepQueue\Exceptions\UnexpectedDeepQueueException;


class Queue implements IQueue
{
	/** @var Remote\IRemoteDequeue */
	private $remoteDequeue;
	
	/** @var Remote\IRemoteEnqueue */
	private $remoteEnqueue;
	
	/** @var ILogger */
	private $logger;

	private $name;

	/** @var IQueueConfig */
	private $config = null;


	private function log(\Throwable $e, string $operation, array $data): void
	{
		$message = "Error: Failed to {$operation} data for {$this->name} queue.";
		$this->logger->logException($e, $message, $data, $this->name);
	}
	
	
	/**
	 * @param Remote\IRemoteDequeue|Remote\IRemoteQueue $queue
	 * @param ILogger $logger
	 * @param Remote\IRemoteEnqueue|null $enqueue
	 */
	public function __construct(string $name, $queue, ILogger $logger, Remote\IRemoteEnqueue $enqueue = null)
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
		
		$this->logger = $logger;
		$this->name = $name;
	}

	
	public function setConfiguration(IQueueConfig $config): void
	{
		$this->config = $config;
	}

	/**
	 * @return Workload[]
	 */
	public function dequeueWorkload(int $count, ?float $waitSeconds = null): array
	{
		try
		{
			return $this->remoteDequeue->dequeueWorkload($count, $this->config, $waitSeconds);
		}
		catch (\Throwable $e)
		{
			$this->log($e, 'dequeue', [
				'queue'			=> $this->name,
				'count'			=> $count,
				'waitSeconds'	=> $waitSeconds
			]);
		}
				
		return [];
	}
	
	/**
	 * @return mixed|null
	 */
	public function dequeueOnce(?float $waitSeconds = null)
	{
		$res = $this->dequeueWorkload(1, $waitSeconds);
		return ($res ? ($res[0])->Payload : null);
	}
	
	public function dequeueWorkloadOnce(?float $waitSeconds = null, float $bufferDelay = 0.0): ?Workload
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
				if (substr($workload->Id, 0, 3) === 'unq')
				{
					$payload[] = $workload->Payload;
				}
				else
				{
					$payload[$workload->Id] = $workload->Payload;
				}
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
		
		try
		{
			$ids = $this->remoteEnqueue->enqueue([$payload]);
			return $ids[0];
		}
		catch (\Throwable $e)
		{
			$this->log($e, 'enqueue', [
				'queue'			=> $this->name,
				'payloadKey'	=> $key,
				'delay'			=> $delay,
				'payload'		=> $payload
			]);
		}
		
		return '';
	}

	/**
	 * @param Payload[]|mixed[] $payloads
	 * @return string[]
	 */
	public function enqueueAll(array $payloads, ?float $delay = null): array
	{
		$hasDelay = !is_null($delay);
		$parsedPayloads = [];
		
		foreach ($payloads as $key => $payload)
		{
			if (!($payload instanceof Payload))
			{
				$payload = new Payload($payload);
				
				if (is_string($key))
				{
					$payload->Key = $key;
				}
			}
			
			if ($hasDelay)
			{
				$payload->Delay = $delay;
			}
			
			$parsedPayloads[] = $payload;
		}
		
		try
		{
			return $this->remoteEnqueue->enqueue($parsedPayloads);
		}
		catch (\Throwable $e)
		{
			$this->log($e, 'enqueueAll', [
				'queue'			=> $this->name,
				'payloadsCount'	=> count($payloads),
				'delay'			=> $delay
			]);
		}
					
		return [];
	}
}