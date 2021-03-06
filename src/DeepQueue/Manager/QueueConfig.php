<?php
namespace DeepQueue\Manager;


use DeepQueue\Base\IQueueConfig;
use DeepQueue\Enums\Policy;

use Objection\LiteSetup;
use Objection\LiteObject;


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
			'MaxBulkSize'		=> LiteSetup::createInt(256),
			'DelayBuffer'		=> LiteSetup::createDouble(0.0),
			'PackageSize'		=> LiteSetup::createInt(0)
		];
	}
}