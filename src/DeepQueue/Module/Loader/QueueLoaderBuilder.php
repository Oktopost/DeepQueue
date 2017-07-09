<?php
namespace DeepQueue\Module\Loader;


use DeepQueue\Enums\QueueLoaderPolicy;
use DeepQueue\Base\Utils\IDecoratorBuilder;
use DeepQueue\Base\Loader\IQueueObjectLoader;
use DeepQueue\Base\Loader\IQueueLoaderBuilder;
use DeepQueue\Base\Loader\Remote\IRemoteQueueObjectLoader;


/**
 * @autoload
 */
class QueueLoaderBuilder implements IQueueLoaderBuilder
{
	/** @var IDecoratorBuilder[] */
	private $builders = [];
	
	/** @var IRemoteQueueObjectLoader */
	private $remoteLoader;
	
	/** @var int|QueueLoaderPolicy */
	private $newQueuePolicy = QueueLoaderPolicy::CREATE_NEW;
	
	
	private function buildChain(IQueueObjectLoader $loader): IQueueObjectLoader
	{
		$last = $loader;
		
		for ($i = count($this->builders) - 1; $i >= 0; $i--)
		{
			$decorator = $this->builders[$i]->buildForLoader();
			$decorator->setChildLoader($last);
			$last = $decorator;
		}
		
		return $last;
	}
	
	private function getLoader($name): IQueueObjectLoader
	{
		return new QueueObjectLoader($this->remoteLoader, $name, $this->newQueuePolicy);
	}
	
	public function addBuilder(IDecoratorBuilder $builder): void
	{
		$this->builders[] = $builder;
	}
	
	public function setRemoteLoader(IRemoteQueueObjectLoader $loader): void
	{
		$this->remoteLoader = $loader;
	}
	
	public function setNewQueuePolicy(int $newQueuePolicy): void
	{
		$this->newQueuePolicy = $newQueuePolicy;
	}

	public function getRemoteLoader(string $name): IQueueObjectLoader
	{
		$loader = $this->getLoader($name);
		
		return ($this->builders ? $this->buildChain($loader) : $loader);
	}
}