<?php
namespace DeepQueue\Plugins\FallbackConnector\Base;


use DeepQueue\Base\Queue\Remote\IRemoteQueue;


interface IFallbackQueue extends IRemoteQueue
{
	public function __construct(string $name, IRemoteQueue $main, IRemoteQueue $fallback);
}