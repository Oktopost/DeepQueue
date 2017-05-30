<?php
namespace DeepQueue\Module\Connector;


use DeepQueue\Base\Stream\IQueueStream;
use DeepQueue\Base\Connector\IConnector;
use DeepQueue\Base\Connector\IConnectorsContainer;


class Container implements IConnectorsContainer
{
	use \DeepQueue\Base\Connector\TConnector;

	
	private const CACHE_SIZE	= 32;
	
	
	/** @var IConnector */
	private $connector;
	
	/** @var IQueueStream[] */
	private $container;
	
	
	public function setConnector(IConnector $connector)
	{
		$this->connector = $connector;
		return $this;
	}
	

	public function getStream(string $name): IQueueStream
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