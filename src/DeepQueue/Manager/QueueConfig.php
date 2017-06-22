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
			'MinimalDelay'		=> LiteSetup::createDouble(),
			'MaximalDelay'		=> LiteSetup::createDouble(),
			'DefaultDelay'		=> LiteSetup::createDouble(),
			'MaxBulkSize'		=> LiteSetup::createInt(256)
		];
	}
}