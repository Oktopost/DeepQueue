<?php
namespace DeepQueue\Plugins\Admin;


use DeepQueue\DeepQueue;
use DeepQueue\Base\IMetaData;
use DeepQueue\Base\IQueueObject;
use DeepQueue\Base\Plugins\IAdminPlugin;
use DeepQueue\Enums\QueueState;


class QueueAdmin implements IAdminPlugin
{
	/** @var DeepQueue */
	private $deepQueue;
	
	
	public function __construct(DeepQueue $deepQueue)
	{
		$this->deepQueue = $deepQueue;
	}


	public function getQueues(): array
	{
		return $this->deepQueue->config()->manager()->loadAll();
	}

	public function getQueue(string $id): ?IQueueObject
	{
		return $this->deepQueue->config()->manager()->loadById($id);
	}

	public function getMetaData(string $queueId): ?IMetaData
	{
		$queueObject = $this->deepQueue->config()->manager()->loadById($queueId);
		
		return $queueObject ? $queueObject->getMetaData() : null;
	}

	public function updateState(string $queueId, string $state): bool
	{
		$queueObject = $this->deepQueue->config()->manager()->loadById($queueId);
		
		if (!$queueObject || !in_array($state, QueueState::getAll()))
			return false;
		
		$queueObject->State = $state;
		
		$this->deepQueue->config()->manager()->update($queueObject);
		
		return true;
	}
}