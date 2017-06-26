<?php
namespace DeepQueue\Base\Plugins\RemoteElements;


use DeepQueue\Base\IMetaData;


interface IQueueManager
{
	public function getMetadata(): IMetaData;
}