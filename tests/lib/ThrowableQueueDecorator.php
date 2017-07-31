<?php
namespace lib;


use DeepQueue\Base\IQueueConfig;
use DeepQueue\Base\Loader\IQueueObjectLoader;
use DeepQueue\Base\Queue\Remote\IRemoteQueue;
use DeepQueue\Base\Connector\Decorator\IRemoteQueueDecorator;


class ThrowableQueueDecorator implements IRemoteQueueDecorator
{
	public function dequeueWorkload(int $count = 1, IQueueConfig $config, ?float $waitSeconds = null): array
	{
		throw new \Exception();
	}

	public function enqueue(array $payload): array
	{
		throw new \Exception();
	}

	public function setRemoteQueue(IRemoteQueue $queue): void
	{
		return;
	}

	public function setQueueLoader(IQueueObjectLoader $loader): void
	{
		return;
	}
}