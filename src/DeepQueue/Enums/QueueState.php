<?php
namespace DeepQueue\Enums;


class QueueState
{
	use \Objection\TEnum;


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
	 * Queue is migrating from one type to another.
	 */
	public const MIGRATING	= 'migrating';

	/**
	 * Queue can not be started again.
	 */
	public const DELETED	= 'deleted';
}