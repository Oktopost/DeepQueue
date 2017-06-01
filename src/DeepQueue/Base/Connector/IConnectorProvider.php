<?php
namespace DeepQueue\Base\Connector;


use DeepQueue\Base\Queue\Remote\IRemoteQueue;


interface IConnectorProvider
{
	public function getRemoteQueue(string $name): IRemoteQueue;
}