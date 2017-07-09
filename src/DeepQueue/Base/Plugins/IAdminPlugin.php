<?php
namespace DeepQueue\Base\Plugins;


use DeepQueue\Base\IMetaData;
use DeepQueue\Base\IQueueObject;


interface IAdminPlugin
{
	/**
	 * @return IQueueObject[]|array
	 */
	public function getQueues(): array;
	public function getQueue(string $id): ?IQueueObject;
	public function getMetaData(string $queueId): ?IMetaData;
	
	public function updateState(string $queueId, string $state): bool;
}