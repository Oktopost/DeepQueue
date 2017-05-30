<?php
namespace DeepQueue\Base\Connector;


use DeepQueue\Base\Stream\IDequeue;
use DeepQueue\Base\Stream\IEnqueue;
use DeepQueue\Base\Stream\IQueueStream;


interface IConnector
{
	public function getStream(string $name): IQueueStream;
	public function getEnqueue(string $name): IEnqueue;
	public function getDequeue(string $name): IDequeue;
}