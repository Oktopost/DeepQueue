<?php
namespace DeepQueue\Plugins\Logger\Log;


use DeepQueue\Plugins\Logger\Enum\LogLevelName;

use Objection\LiteObject;
use Objection\LiteSetup;


/**
 * @property string	Id
 * @property string	ParentId
 * @property int	Level
 * @property int	Time
 * @property string	Message
 * @property mixed	Data
 */
class LogEntry extends LiteObject
{
	protected function _setup()
	{
		return [
			'Id'		=> LiteSetup::createString(),
			'ParentId'	=> LiteSetup::createString(null),
			'Level'		=> LiteSetup::createEnum(array_values(LogLevelName::MAP)),
			'Time'		=> LiteSetup::createInt(),
			'Message'	=> LiteSetup::createString(),
			'Data'		=> LiteSetup::createMixed()
		];
	}
}