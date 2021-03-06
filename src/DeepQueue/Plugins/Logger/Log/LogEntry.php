<?php
namespace DeepQueue\Plugins\Logger\Log;


use DeepQueue\Plugins\Logger\Enum\LogLevelName;

use Objection\LiteSetup;
use Objection\LiteObject;


/**
 * @property string		$Id
 * @property \DateTime	$Created
 * @property string		$ParentId
 * @property string		$QueueName
 * @property int		$Level
 * @property string		$Message
 * @property mixed		$Data
 */
class LogEntry extends LiteObject
{
	protected function _setup()
	{
		return [
			'Id'			=> LiteSetup::createString(),
			'Created'		=> LiteSetup::createDateTime(),
			'ParentId'		=> LiteSetup::createString(null),
			'QueueName'		=> LiteSetup::createString(null),
			'Level'			=> LiteSetup::createEnum(array_values(LogLevelName::MAP)),
			'Message'		=> LiteSetup::createString(),
			'Data'			=> LiteSetup::createMixed()
		];
	}
}