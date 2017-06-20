<?php
namespace DeepQueue\Base\Connector\Remote;


use DeepQueue\Base\Queue\Remote\IRemoteQueue;


interface IRemoteQueueProvider
{
	public function getQueue(string $name): IRemoteQueue;
}