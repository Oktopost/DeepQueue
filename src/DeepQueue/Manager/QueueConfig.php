<?php
namespace DeepQueue\Manager;


use DeepQueue\Enums\Policy;
use DeepQueue\Base\IQueueConfig;

use Objection\LiteObject;
use Objection\LiteSetup;


class QueueConfig extends LiteObject implements IQueueConfig
{
	protected function _setup()
	{
		return [
			'UniqueKeyPolicy' 	=> LiteSetup::createString(Policy::ALLOWED),
			'DelayPolicy'		=> LiteSetup::createString(Policy::IGNORED),
			'MinimalDelay'		=> LiteSetup::createDouble(-1),
			'MaximalDelay'		=> LiteSetup::createDouble(-1),
			'DefaultDelay'		=> LiteSetup::createDouble(-1),
			'MaxBulkSize'		=> LiteSetup::createInt(256)
		];
	}
}