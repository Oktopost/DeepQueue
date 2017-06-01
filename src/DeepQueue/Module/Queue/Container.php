<?php
namespace DeepQueue\Module\Queue;


use DeepQueue\Base\Queue\IQueue;
use DeepQueue\Base\Queue\Connector\IConnector;
use DeepQueue\Base\Queue\Connector\IConnectorsContainer;


class Container implements IConnectorsContainer
{
	use \DeepQueue\Base\Queue\Connector\TConnector;

	
	private const CACHE_SIZE	= 32;
	
	
	/** @var IConnector */
	private $connector;
	
	/** @var IQueue[] */
	private $container;
	
	
	public function setConnector(IConnector $connector)
	{
		$this->connector = $connector;
		return $this;
	}
	

	public function getStream(string $name): IQueue
	{
		if (!isset($this->container[$name]))
		{
			$queue = $this->connector->getStream($name);
			
			if (count($this->container) >= self::CACHE_SIZE)
			{
				$this->container = [];
			}
			
			$this->container[$name] = $queue;
		}
		
		return $this->container[$name];
	}
}