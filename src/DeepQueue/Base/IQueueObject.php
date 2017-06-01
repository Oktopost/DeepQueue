<?php
namespace DeepQueue\Base;


use DeepQueue\Base\Queue\IQueue;
use DeepQueue\Enums\QueueState;


/**
 * @property string				$ID
 * @property string				$Name
 * @property string|QueueState	$State
 * @property IQueueConfig		$Config
 */
interface IQueueObject
{
	public function getStream(): IQueue;
	public function getMetaData(): IMetaData;
}