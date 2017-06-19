<?php
namespace DeepQueue\Module\Loader;


use DeepQueue\Base\Loader\IQueueLoaderBuilder;
use DeepQueue\Base\Loader\IQueueObjectLoader;
use DeepQueue\Base\Loader\IRemoteQueueObjectLoader;
use DeepQueue\Base\Loader\Decorator\ILoaderDecoratorBuilder;
use DeepQueue\Module\Connector\LoaderDecorators\QueueObjectLoader;


class LoaderBuilder implements IQueueLoaderBuilder
{
	/** @var ILoaderDecoratorBuilder[] */
	private $builders = [];
	
	/** @var IRemoteQueueObjectLoader */
	private $loader;
	
	
	private function buildChain(IQueueObjectLoader $loader): IQueueObjectLoader
	{
		$last = $loader;
		
		for ($i = count($this->builders) - 1; $i >= 0; $i--)
		{
			$decorator = $this->builders[$i]->build();
			$decorator->setChildLoader($last);
			$last = $decorator;
		}
		
		return $last;
	}
	
	private function getLoader($name, $newQueuePolicy): IQueueObjectLoader
	{
		return new QueueObjectLoader($this->loader, $name, $newQueuePolicy);
	}
	
	public function addBuilder(ILoaderDecoratorBuilder $builder): void
	{
		$this->builders[] = $builder;
	}
	
	public function setRemoteLoader(IRemoteQueueObjectLoader $loader): void
	{
		$this->loader = $loader;
	}

	public function getRemoteLoader(string $name, $newQueuePolicy): IQueueObjectLoader
	{
		$loader = $this->getLoader($name, $newQueuePolicy);
		
		return ($this->builders ? $this->buildChain($loader) : $loader);
	}
}