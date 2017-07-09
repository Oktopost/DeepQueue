<?php
namespace DeepQueue\Plugins\Connectors\FallbackConnector\Base;


use DeepQueue\Base\Queue\Remote\IRemoteQueue;
use DeepQueue\Plugins\Logger\Base\ILogger;


interface IFallbackQueue extends IRemoteQueue
{
	public function __construct(string $name, IRemoteQueue $main, IRemoteQueue $fallback, ILogger $logger);
}