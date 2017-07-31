<?php
namespace DeepQueue\Base\Queue;


use DeepQueue\Base\IQueueConfig;

interface IQueue extends IEnqueue, IDequeue
{
	public function setConfiguration(IQueueConfig $config): void;
}