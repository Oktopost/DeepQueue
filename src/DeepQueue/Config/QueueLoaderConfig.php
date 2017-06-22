<?php
namespace DeepQueue\Config;


use DeepQueue\Scope;
use DeepQueue\Base\Config\IQueueLoaderConfig;
use DeepQueue\Base\Loader\IQueueLoaderBuilder;
use DeepQueue\Base\Loader\IQueueObjectLoader;
use DeepQueue\Base\Loader\Decorator\ILoaderDecoratorBuilder;
use DeepQueue\Base\Plugins\IManagerPlugin;
use DeepQueue\Module\Loader\Builder\LoaderClassNameBuilder;
use DeepQueue\Module\Loader\Decorators\CachedLoaderDecorator;
use DeepQueue\Exceptions\InvalidUsageException;


class QueueLoaderConfig implements IQueueLoaderConfig
{
	/** @var IQueueLoaderBuilder */
	private $loaderBuilder;
	
	/** @var IManagerPlugin */
	private $manager;
	
	private $queueNotExistsPolicy;
	
	private function createLoaderBuilder(): IQueueLoaderBuilder
	{
		$this->loaderBuilder = Scope::skeleton(IQueueLoaderBuilder::class);
		
		$this->loaderBuilder->setRemoteLoader($this->manager);
		$this->loaderBuilder->setNewQueuePolicy($this->queueNotExistsPolicy);
		
		$this->addLoaderBuilder(
			CachedLoaderDecorator::class	
		);
		
		return $this->loaderBuilder;
	}
	
	
	public function setManager(IManagerPlugin $manager): IQueueLoaderConfig
	{
		$this->manager = $manager;
		
		return $this;
	}
	
	public function setQueueNotExistsPolicy(int $queueNotExistsPolicy): IQueueLoaderConfig
	{
		$this->queueNotExistsPolicy = $queueNotExistsPolicy;
		
		return $this;
	}

	/**
	 * @param string|ILoaderDecoratorBuilder[] $builders
	 */
	public function addLoaderBuilder(...$builders): void
	{
		foreach ($builders as $builder)
		{
			if (is_array($builder))
			{
				$this->addLoaderBuilder(...$builder);
			}
			else if (is_string($builder))
			{
				$this->loaderBuilder->addBuilder(new LoaderClassNameBuilder($builder));
			}
			else if ($builder instanceof ILoaderDecoratorBuilder)
			{
				$this->loaderBuilder->addBuilder($builder); 
			}
			else
			{
				throw new InvalidUsageException('Parameter must be string, array or ILoaderDecoratorBuilder instance!');
			}
		}
	}
	
	public function getQueueLoader(string $name): IQueueObjectLoader
	{
		if (!$this->loaderBuilder)
		{
			return $this->createLoaderBuilder()->getRemoteLoader($name);
		}
		
		return $this->loaderBuilder->getRemoteLoader($name);
	}
	
	public function getLoaderBuilder(): IQueueLoaderBuilder
	{
		if (!$this->loaderBuilder)
		{
			return $this->createLoaderBuilder();
		}
		
		return $this->loaderBuilder;
	}
}