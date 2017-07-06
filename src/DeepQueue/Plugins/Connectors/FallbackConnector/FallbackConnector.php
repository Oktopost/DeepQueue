<?php
namespace DeepQueue\Plugins\Connectors\FallbackConnector;


use DeepQueue\Base\IMetaData;
use DeepQueue\Base\IQueueObject;
use DeepQueue\Base\IDeepQueueConfig;
use DeepQueue\Base\Plugins\IConnectorPlugin;
use DeepQueue\Base\Queue\Remote\IRemoteQueue;
use DeepQueue\Plugins\Connectors\FallbackConnector\Base\IFallbackConnector;
use DeepQueue\Plugins\Connectors\FallbackConnector\Queue\FallbackQueue;


class FallbackConnector implements IFallbackConnector
{
	/** @var IConnectorPlugin */
	private $main;
	
	/** @var IConnectorPlugin */
	private $fallback;
	
	
	public function __construct(IConnectorPlugin $main, IConnectorPlugin $fallback)
	{
		$this->main = $main;
		$this->fallback = $fallback;
	}

	
	public function setDeepConfig(IDeepQueueConfig $config): void
	{
		$this->main->setDeepConfig($config);
		$this->fallback->setDeepConfig($config);
	}

	public function getMetaData(IQueueObject $queueObject): IMetaData
	{
		try
		{
			return $this->main->getMetaData($queueObject);
		}
		catch (\Throwable $e)
		{
			//TODO: log exception
			return $this->fallback->getMetaData($queueObject);
		}
	}

	public function getQueue(string $name): IRemoteQueue
	{
		return new FallbackQueue($name, $this->main->getQueue($name), $this->fallback->getQueue($name));
	}
}