<?php
namespace DeepQueue\Plugins\MySQLConnector\Queue;


use DeepQueue\Payload;
use DeepQueue\Workload;
use DeepQueue\Base\Queue\Remote\IRemoteQueue;
use DeepQueue\Utils\TimeGenerator;
use DeepQueue\Utils\PayloadConverter;
use DeepQueue\Plugins\Logger\Base\ILogger;
use DeepQueue\Plugins\MySQLConnector\Base\DAO\IMySQLQueueDAO;

use Serialization\Base\ISerializer;


class MySQLQueue implements IRemoteQueue
{
	private $name;

	/** @var IMySQLQueueDAO */
	private $dao;
	
	/** @var PayloadConverter */
	private $converter;
	
	/** @var ILogger */
	private $logger;
	
	
	private function getPayloads(int $count): array 
	{
		return $this->dao->dequeue($this->name, $count);
	}
	
	private function getPayloadsWithWaiting(int $count, float $waitSeconds)
	{
		//TODO:: waiting for MySQL with sleep
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
	
	
	public function __construct(string $name, IMySQLQueueDAO $dao, ISerializer $serializer, ILogger $logger)
	{
		$this->dao = $dao;
		$this->name = $name;
		$this->converter = new PayloadConverter($serializer);
		$this->logger = $logger;
	}
	
	
	/**
	 * @return Workload[]|array
	 */
	public function dequeueWorkload(int $count = 1, ?float $waitSeconds = null): array
	{
		if ($waitSeconds > 0)
		{
			$payloads = $this->getPayloadsWithWaiting($count, $waitSeconds);
		}
		else
		{
			$payloads = $this->getPayloads($count);
		}

		return $this->converter->getWorkloads($payloads);
	}

	/**
	 * @param Payload[] $payload
	 * @return string[]|array IDs for each payload
	 */
	public function enqueue(array $payload): array
	{
		$prepared = $this->converter->prepareAll($payload);
		
		return $this->dao->enqueue($this->name, $prepared);
	}
}