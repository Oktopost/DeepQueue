<?php
namespace DeepQueue\Module\Connector;


use DeepQueue\Base\Loader\IQueueObjectLoader;
use DeepQueue\Base\Queue\Remote\IRemoteQueue;
use DeepQueue\Base\Plugins\IRemotePlugin;
use DeepQueue\Base\Connector\IConnectorBuilder;
use DeepQueue\Base\Connector\Decorator\IDecoratorBuilder;


class ConnectorBuilder implements IConnectorBuilder
{
	/** @var IRemotePlugin */
	private $plugin;
	
	/** @var IDecoratorBuilder[] */
	private $builders = [];
	
	/** @var IQueueObjectLoader */
	private $loader;
	
	
	private function buildChain(IRemoteQueue $queue): IRemoteQueue
	{
		$last = $queue;
		
		for ($i = count($this->builders) - 1; $i >= 0; $i--)
		{
			$decorator = $this->builders[$i]->build();
			$decorator->setRemoteQueue($last);
			$decorator->setQueueLoader($this->loader);
			$last = $decorator;
		}
		
		return $last;
	}
	
	
	public function addBuilder(IDecoratorBuilder $builder): void
	{
		$this->builders[] = $builder;
	}
	
	public function setLoader(IQueueObjectLoader $loader): void
	{
		$this->loader = $loader;
	}
	
	public function setPlugin(IRemotePlugin $remote): void
	{
		$this->plugin = $remote;
	}
	
	public function getRemoteQueue(string $name): IRemoteQueue
	{
		$remoteQueue = $this->plugin->getQueue($name);
		
		return ($this->builders ? $this->buildChain($remoteQueue) : $remoteQueue);
	}
}