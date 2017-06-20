<?php
namespace DeepQueue\Plugins\InMemoryRemote;


use DeepQueue\Base\Plugins\RemoteElements\IMetaDataDAO;
use DeepQueue\Base\Queue\Remote\IRemoteQueue;
use DeepQueue\Plugins\InMemoryRemote\Base\IInMemoryRemote;
use DeepQueue\Plugins\InMemoryRemote\Queue\InMemoryRemoteQueue;


class InMemoryRemote implements IInMemoryRemote
{
	public function getMetaDataDAO(): IMetaDataDAO
	{
		// TODO: Implement getMetaDataDAO() method.
	}

	public function getQueue(string $name): IRemoteQueue
	{
		return new InMemoryRemoteQueue($name);
	}

}