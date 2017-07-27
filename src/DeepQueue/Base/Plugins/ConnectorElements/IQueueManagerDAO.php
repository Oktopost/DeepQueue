<?php
namespace DeepQueue\Base\Plugins\ConnectorElements;


interface IQueueManagerDAO
{
	public function countEnqueued(string $queueName): int;
	public function countDelayed(string $queueName): int;
	public function countNotDelayed(string $queueName): int;
	
	public function countDelayedReadyToDequeue(string $queueName): int;
	public function getFirstDelayed(string $queueName): array;
	public function getDelayedElementByIndex(string $queueName, int $index): array;
	
	public function clearQueue(string $queueName): void;
	public function flushDelayed(string $queueName): void;
}