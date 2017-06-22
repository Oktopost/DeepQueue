<?php
namespace DeepQueue\Plugins\InMemoryConnector\Base;


use DeepQueue\Base\IMetaData;


interface IInMemoryQueueManager
{
	public function getMetadata(): IMetaData;
}