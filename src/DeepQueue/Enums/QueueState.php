<?php
namespace DeepQueue\Enums;


use Traitor\TEnum;


class QueueState
{
	use TEnum;


	/**
	 * Normal queue operation.
	 */
	public const RUNNING	= 'running';

	/**
	 * Enqueing data will still be available, but any dequeing is stalled until the queue is running again.
	 */
	public const PAUSED		= 'paused';

	/**
	 * Any enqueued data is ignored and the queue is cleared as soon as this status is applied. 
	 */
	public const STOPPED	= 'stopped';

	/**
	 * Queue can not be started again.
	 */
	public const DELETED	= 'deleted';
	
	
	public const EXISTING = [
		self::RUNNING,
		self::PAUSED,
		self::STOPPED
	];
}