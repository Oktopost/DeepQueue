<?php
namespace DeepQueue\Plugins\InMemoryManager\Queue;


use DeepQueue\Base\IMetaData;
use DeepQueue\Base\IQueueObject;
use DeepQueue\Base\Queue\IQueue;
use DeepQueue\Enums\QueueState;

use Objection\LiteObject;
use Objection\LiteSetup;


class InMemoryQueue extends LiteObject implements IQueueObject
{
	protected function _setup()
	{
		return [
			'ID'		=> LiteSetup::createString(),
			'Name'		=> LiteSetup::createString(),
			'State'		=> LiteSetup::createString(QueueState::PAUSED),
			'Config'	=> LiteSetup::createInstanceOf(InMemoryQueueConfig::class)
		];
	}

	
	public function getStream(): IQueue
	{
		// TODO: Implement getStream() method.
	}

	public function getMetaData(): IMetaData
	{
		// TODO: Implement getMetaData() method.
	}
}