<?php
namespace DeepQueue\Plugins\Connectors\MySQLConnector\Queue;


use DeepQueue\Payload;
use DeepQueue\Workload;
use DeepQueue\Base\IQueueConfig;
use DeepQueue\Base\Queue\Remote\IRemoteQueue;
use DeepQueue\Utils\PayloadConverter;
use DeepQueue\Plugins\Logger\Base\ILogger;
use DeepQueue\Plugins\Connectors\MySQLConnector\Base\DAO\IMySQLQueueDAO;
use DeepQueue\Plugins\Connectors\MySQLConnector\Converter\MySQLPayloadConverter;

use Serialization\Base\ISerializer;


class MySQLQueue implements IRemoteQueue
{
	private const MAX_SLEEP_TIME = 5;
	
	
	private $name;

	/** @var IMySQLQueueDAO */
	private $dao;
	
	/** @var PayloadConverter */
	private $converter;
	
	/** @var ILogger */
	private $logger;
	
	
	private function log(array $payloads, $operation): void
	{
		$message = ucfirst($operation) . " in {$this->name} queue payload with id: ";
		
		foreach ($payloads as $payload)
		{
			$this->logger->info($message . $payload['Id'], [
				'payload' 	=> $payload['Payload'],
				'queue'		=> $this->name
			], $payload['Id'], $this->name);
		}
	}
	
	private function getPayloads(int $count, int $bufferDelay, int $packageSize): array 
	{
		return $this->dao->dequeue($this->name, $count, $bufferDelay, $packageSize);
	}
	
	private function getPayloadsWithWaiting(int $count, float $waitSeconds, int $bufferDelay, int $packageSize)
	{
		$waitSeconds = (int)floor($waitSeconds);

		$payloads = [];

		while ($waitSeconds >= 0)
		{
			$payloads = $this->getPayloads($count, $bufferDelay, $packageSize);
			
			$sleepTime = min($waitSeconds, self::MAX_SLEEP_TIME);

			if ($payloads || !$sleepTime)
			{
				break;
			}

			sleep($sleepTime);
			
			$waitSeconds -= $sleepTime;
		}
		
		return $payloads;
	}
	
	
	public function __construct(string $name, IMySQLQueueDAO $dao, ISerializer $serializer, ILogger $logger)
	{
		$this->dao = $dao;
		$this->name = $name;
		$this->converter = new MySQLPayloadConverter($serializer);
		$this->logger = $logger;
	}
	
	
	/**
	 * @return Workload[]|array
	 */
	public function dequeueWorkload(int $count = 1, IQueueConfig $config, ?float $waitSeconds = null): array
	{
		$buffer = (int)floor($config->DelayBuffer);
		
		if ($waitSeconds > 0)
		{
			$payloads = $this->getPayloadsWithWaiting($count, $waitSeconds, $buffer, $config->PackageSize);
		}
		else
		{
			$payloads = $this->getPayloads($count, $buffer, $config->PackageSize);
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
		$prepared = $this->converter->prepareAll($this->name, $payload);
		
		$ids = $this->dao->enqueue($this->name, $prepared);
		
		if ($ids)
		{
			$this->log($prepared['payloads'], 'enqueue');
		}
		
		return $ids;
	}
}