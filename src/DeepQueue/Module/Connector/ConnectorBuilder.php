<?php
namespace DeepQueue\Module\Connector;


use DeepQueue\Base\Queue\Remote\IRemoteQueue;
use DeepQueue\Base\Plugins\IRemotePlugin;
use DeepQueue\Base\Connector\IConnectorBuilder;
use DeepQueue\Base\Connector\IQueueLoaderBuilder;
use DeepQueue\Base\Connector\Loader\IQueueLoader;
use DeepQueue\Base\Connector\Decorator\IDecoratorBuilder;


class ConnectorBuilder implements IConnectorBuilder
{
	/** @var IRemotePlugin */
	private $plugin;
	
	/** @var IDecoratorBuilder[] */
	private $builders = [];
	
	/** @var IQueueLoaderBuilder */
	private $loaderBuilder;
	
	
	private function buildChain(IRemoteQueue $queue, IQueueLoader $loader): IRemoteQueue
	{
		$last = $queue;
		
		for ($i = count($this->builders) - 1; $i >= 0; $i--)
		{
			$decorator = $this->builders[$i]->build();
			$decorator->setRemoteQueue($last);
			$decorator->setQueueLoader($loader);
			$last = $decorator;
		}
		
		return $last;
	}
	
	
	public function addBuilder(IDecoratorBuilder $builder): void
	{
		$this->builders[] = $builder;
	}
	
	public function setLoader(IQueueLoaderBuilder $builder): void
	{
		$this->loaderBuilder = $builder;
	}
	
	public function getRemoteQueue(string $name): IRemoteQueue
	{
		$remoteQueue = $this->plugin->getQueue($name);
		$loader = $this->loaderBuilder->create($name);
		
		return ($this->builders ? 
			$this->buildChain($remoteQueue, $loader) : 
			$remoteQueue);
	}
}