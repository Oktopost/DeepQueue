<?php
namespace DeepQueue\Plugins\Connectors\SoftSwitchConnector;


use DeepQueue\Base\IDeepQueueConfig;
use DeepQueue\Base\Plugins\IConnectorPlugin;
use DeepQueue\Base\Plugins\ConnectorElements\IQueueManager;
use DeepQueue\Base\Queue\Remote\IRemoteQueue;


class SoftSwitchConnector implements IConnectorPlugin
{
	private $from;
	private $to;
	
	
	public function __construct(IConnectorPlugin $from, IConnectorPlugin $to)
	{
		$this->from = $from;
		$this->to = $to;
	}
	
	
	public function setDeepConfig(IDeepQueueConfig $config): void
	{
		$this->from->setDeepConfig($config);
		$this->to->setDeepConfig($config);
	}
	
	public function manager(string $queueName): IQueueManager
	{
		return $this->to->manager($queueName);
	}
	
	public function getQueue(string $name): IRemoteQueue
	{
		return new SoftSwitchQueue(
			$this->from->getQueue($name), 
			$this->to->getQueue($name)
		);
	}
}