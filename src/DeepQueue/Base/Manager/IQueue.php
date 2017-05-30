<?php
namespace DeepQueue\Base\Manager;


use DeepQueue\Base\Stream\IQueueStream;
use DeepQueue\Enums\QueueState;


/**
 * @property string				$ID
 * @property string				$Name
 * @property string|QueueState	$State
 * @property IConfig			$Config
 */
interface IQueue
{
	public function getStream(): IQueueStream;
	public function getMetaData(): IMetaData;
}