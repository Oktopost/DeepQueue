<?php
namespace DeepQueue\Base\Plugins\ConnectorElements;


use DeepQueue\Base\IMetaData;


interface IQueueManager
{
	public function setQueueName(string $queueName): void;
	
	public function getMetaData(): IMetaData;
	public function clearQueue(): void;
}