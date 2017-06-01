<?php
namespace DeepQueue\Base\Plugins\RemoteElements;


use DeepQueue\Base\IQueueObject;


interface IMetaDataDAO
{
	/**
	 * @param IQueueObject[] $queues
	 */
	public function populate(array $queues): void;
}