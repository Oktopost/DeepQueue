<?php
namespace DeepQueue\Base\Plugins\ConnectorElements;


use DeepQueue\Base\IMetaData;


interface IQueueManager
{
	public function getMetadata(): IMetaData;
}