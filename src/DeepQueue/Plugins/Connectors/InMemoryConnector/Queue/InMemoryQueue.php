<?php
namespace DeepQueue\Plugins\Connectors\InMemoryConnector\Queue;


use DeepQueue\Plugins\Logger\Base\ILogger;
use DeepQueue\Scope;
use DeepQueue\Payload;
use DeepQueue\Workload;
use DeepQueue\Utils\TimeGenerator;
use DeepQueue\Utils\PayloadConverter;
use DeepQueue\Base\Queue\Remote\IRemoteQueue;
use DeepQueue\Plugins\Connectors\InMemoryConnector\Base\IInMemoryQueueDAO;

use Serialization\Base\ISerializer;


class InMemoryQueue implements IRemoteQueue
{
	private $name;

	/** @var IInMemoryQueueDAO */
	private $dao;
	
	/** @var PayloadConverter */
	private $converter;
	
	/** @var ILogger */
	private $logger;

	/** @var bool */
	private $isErrorsEnabled;
	
	
	private function log(array $data, $operation): void
	{
		$message = ucfirst($operation) . " in {$this->name} queue payload with id: ";
		
		foreach ($data as $key => $payload)
		{
			$this->logger->info($message . $key, [
				'payload' 	=> $payload,
				'queue'		=> $this->name
			], $key, $this->name);
		}
	}
	
	private function getPayloads(int $count): array 
	{
		return $this->dao->dequeue($this->name, $count);
	}
	
	private function getPayloadsWithWaiting(int $count, float $waitSeconds)
	{
		$endTime = TimeGenerator::getMs($waitSeconds);
		$nowTime = TimeGenerator::getMs();

		$payloads = [];

		while ($nowTime < $endTime)
		{
			$payloads = $this->getPayloads($count);
			
			if ($payloads)
			{
				break;
			}
			
			$nowTime = TimeGenerator::getMs();
		}
		
		return $payloads;
	}
	
	private function throwErrorWithRand(): void
	{
		$rand = (float)rand()/(float)getrandmax();
		if ($rand < 0.2)
		{
			throw new \Exception('Error for debug');
		}
	}
	
	
	public function __construct(string $name, ISerializer $serializer, ILogger $logger, $enableErrors = false)
	{
		$this->dao = Scope::skeleton(IInMemoryQueueDAO::class);
		$this->converter = new PayloadConverter($serializer);
		$this->name = $name;
		$this->logger = $logger;
		$this->isErrorsEnabled = $enableErrors;
	}

	
	/**
	 * @return Workload[]
	 */
	public function dequeueWorkload(int $count = 1, ?float $waitSeconds = null): array
	{
		if ($this->isErrorsEnabled)
		{
			$this->throwErrorWithRand();
		}
		
		if ($waitSeconds > 0)
		{
			$payloads = $this->getPayloadsWithWaiting($count, $waitSeconds);
		}
		else
		{
			$payloads = $this->getPayloads($count);
		}
		
		if ($payloads)
		{
			$this->log($payloads, 'dequeue');
		}

		return $this->converter->getWorkloads($payloads);
	}

	/**
	 * @param Payload[] $payload
	 * @return string[]|array IDs for each payload
	 */
	public function enqueue(array $payload): array
	{
		if ($this->isErrorsEnabled)
		{
			$this->throwErrorWithRand();
		}
		
		$prepared = $this->converter->prepareAll($payload);
		
		$ids = $this->dao->enqueue($this->name, $prepared['keyValue']);
		
		if ($ids)
		{
			$this->log($prepared['keyValue'], 'enqueue');
		}
		
		return $ids;
	}
}