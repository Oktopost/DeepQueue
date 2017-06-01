<?php
namespace DeepQueue\Base\Validator;


use DeepQueue\Base\Queue\Remote\IRemoteEnqueue;


/**
 * @skeleton
 */
interface IValidator extends IRemoteEnqueue 
{
	public function setLoader(IQueueLoader $loader): void;
	public function setRemoteEnqueue(IRemoteEnqueue $enqueue): void;
}