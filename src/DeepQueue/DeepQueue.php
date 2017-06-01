<?php
namespace DeepQueue;


use DeepQueue\Base\IQueueObject;
use DeepQueue\Base\Queue\IQueue;
use DeepQueue\Base\Queue\Connector\IConnector;
use DeepQueue\Base\Queue\Connector\IConnectorsContainer;
use DeepQueue\Base\Plugins\IRemotePlugin;
use DeepQueue\Base\Plugins\IManagerPlugin;
use DeepQueue\Base\Helpers\IQueueBuilder;


class DeepQueue implements IConnector
{
	use \DeepQueue\Base\Queue\Connector\TConnector;
	
	
	/** @var IRemotePlugin */
	private $remotePlugin;
	
	/** @var IManagerPlugin */
	private $managerPlugin;
	
	/** @var IConnectorsContainer */
	private $container;
	
	/** @var IQueueBuilder */
	private $builder;
	
	
	public function __construct()
	{
		$this->builder = Scope::skeleton(IQueueBuilder::class);
		
		$this->container = Scope::skeleton(IConnectorsContainer::class);
		$this->container->setConnector($this->builder);
	}
	
	
	public function remote(): IRemotePlugin
	{
		return $this->remotePlugin;
	}
	
	public function manager(): IManagerPlugin
	{
		return $this->managerPlugin;
	}
	
	
	public function setRemotePlugin(IRemotePlugin $plugin): DeepQueue
	{
		$this->builder->setRemotePlugin($plugin);
		$this->remotePlugin = $plugin;
		return $this;
	}
	
	public function setManagerPlugin(IManagerPlugin $plugin): DeepQueue
	{
		$this->builder->setQueueDAO($plugin->getQueueDAO());
		$this->managerPlugin = $plugin;
		return $this;
	}
	
	public function getStream(string $name): IQueue
	{
		return $this->container->getStream($name);
	}
	
	public function getQueueObject(string $name): ?IQueueObject
	{
		return $this->managerPlugin->getQueueDAO()->loadIfExists($name);
	}
}