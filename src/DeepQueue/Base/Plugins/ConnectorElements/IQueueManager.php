<?php
namespace DeepQueue\Base\Plugins\ConnectorElements;


use DeepQueue\Base\IMetaData;


interface IQueueManager
{
	public function setQueueName(string $queueName): void;
	
	public function getMetaData(): IMetaData;
	public function getWaitingTime(float $secondsDepth = 0.0, int $bulkSize = 0): ?float;
	
	public function flushDelayed(): void;
	public function clearQueue(): void;
}