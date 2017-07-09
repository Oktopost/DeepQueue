<?php
namespace DeepQueue\Module\Connector;


use DeepQueue\Base\Utils\IDecoratorBuilder;
use DeepQueue\Base\Queue\Remote\IRemoteQueue;
use DeepQueue\Base\Loader\IQueueLoaderBuilder;
use DeepQueue\Base\Connector\IConnectorBuilder;
use DeepQueue\Base\Connector\Remote\IRemoteQueueProvider;


/**
 * @autoload
 */
class ConnectorBuilder implements IConnectorBuilder
{
	/** @var IRemoteQueueProvider */
	private $remoteProvider;
	
	/** @var IDecoratorBuilder[] */
	private $builders = [];
	
	/** @var IQueueLoaderBuilder */
	private $loaderBuilder;
	
	
	private function buildChain(IRemoteQueue $queue, string $name): IRemoteQueue
	{
		$last = $queue;
		
		for ($i = count($this->builders) - 1; $i >= 0; $i--)
		{
			$decorator = $this->builders[$i]->buildForConnector();
			$decorator->setRemoteQueue($last);
			$decorator->setQueueLoader($this->loaderBuilder->getRemoteLoader($name));
			$last = $decorator;
		}
		
		return $last;
	}
	
	
	public function addBuilder(IDecoratorBuilder $builder): void
	{
		$this->builders[] = $builder;
	}
	
	public function setLoaderBuilder(IQueueLoaderBuilder $loaderBuilder): void
	{
		$this->loaderBuilder = $loaderBuilder;
	}
	
	public function setRemoteProvider(IRemoteQueueProvider $remoteProvider): void
	{
		$this->remoteProvider = $remoteProvider;
	}
	
	public function getRemoteQueue(string $name): IRemoteQueue
	{
		$remoteQueue = $this->remoteProvider->getQueue($name);
		
		return ($this->builders ? $this->buildChain($remoteQueue, $name) : $remoteQueue);
	}
}