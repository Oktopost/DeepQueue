<?php
namespace DeepQueue\Plugins\InMemoryManager\Queue;


use DeepQueue\Enums\Policy;
use DeepQueue\Base\IQueueConfig;

use Objection\LiteObject;
use Objection\LiteSetup;


class InMemoryQueueConfig extends LiteObject implements IQueueConfig
{
	protected function _setup()
	{
		return [
			'UniqueKeyPolicy' 	=> LiteSetup::createString(Policy::ALLOWED),
			'DelayPolicy'		=> LiteSetup::createString(Policy::IGNORED),
			'MinimalDelay'		=> LiteSetup::createDouble(),
			'MaximalDelay'		=> LiteSetup::createDouble(),
			'DefaultDelay'		=> LiteSetup::createDouble(),
			'MaxBulkSize'		=> LiteSetup::createInt(256)
		];
	}
}