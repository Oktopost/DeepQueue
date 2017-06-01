<?php
namespace DeepQueue\Base\Queue\Connector;


use DeepQueue\Base\Queue\IQueue;
use DeepQueue\Base\Queue\IDequeue;
use DeepQueue\Base\Queue\IEnqueue;


interface IConnector
{
	public function getStream(string $name): IQueue;
	public function getEnqueue(string $name): IEnqueue;
	public function getDequeue(string $name): IDequeue;
}