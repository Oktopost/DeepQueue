<?php
namespace DeepQueue;


use DeepQueue\Enums\QueueLoaderPolicy;
use DeepQueue\Base\IDeepQueueConfig;
use DeepQueue\Base\Loader\IQueueObjectLoader;
use DeepQueue\Base\Loader\IQueueLoaderBuilder;
use DeepQueue\Base\Loader\Decorator\ILoaderDecoratorBuilder;
use DeepQueue\Base\Plugins\IRemotePlugin;
use DeepQueue\Base\Plugins\IManagerPlugin;
use DeepQueue\Base\Connector\IConnectorBuilder;
use DeepQueue\Base\Connector\IConnectorProvider;
use DeepQueue\Base\Connector\Decorator\IDecoratorBuilder;
use DeepQueue\Module\Connector\Builder\ClassNameBuilder;
use DeepQueue\Module\Connector\Decorators\QueueDataTransformDecorator;
use DeepQueue\Module\Connector\Decorators\QueueStateDecorator;
use DeepQueue\Module\Loader\Builder\LoaderClassNameBuilder;
use DeepQueue\Module\Loader\Decorators\CachedLoaderDecorator;

use DeepQueue\Exceptions\InvalidUsageException;


class DeepQueueConfig implements IDeepQueueConfig 
{
	/** @var IConnectorBuilder */
	private $connectorBuilder = null;
	
	/** @var IQueueLoaderBuilder */
	private $loaderBuilder = null;
	
	/** @var IRemotePlugin */
	private $remotePlugin = null;
	
	/** @var IManagerPlugin */
	private $managerPlugin = null;
	
	/** @var int */
	private $queueNotExistsPolicy = QueueLoaderPolicy::CREATE_NEW;
	
	
	private function createLoaderBuilder(): IQueueLoaderBuilder
	{
		$this->loaderBuilder = Scope::skeleton(IQueueLoaderBuilder::class);
		
		$this->loaderBuilder->setRemoteLoader($this->manager());
		$this->loaderBuilder->setNewQueuePolicy($this->queueNotExistsPolicy);
		
		$this->addLoaderBuilder(
				CachedLoaderDecorator::class	
		);
		
		return $this->loaderBuilder;
	}
	
	private function createConnectorBuilder(): IConnectorBuilder
	{
		$this->connectorBuilder = Scope::skeleton(IConnectorBuilder::class);

		$this->connectorBuilder->setRemoteProvider($this->remote());
		$this->connectorBuilder->setLoaderBuilder($this->createLoaderBuilder());
		
		$this->addConnectorBuilder(
			QueueStateDecorator::class, 
			QueueDataTransformDecorator::class
		);
		
		return $this->connectorBuilder;
	}
	
	
	public function getConnectorProvider(): IConnectorProvider
	{
		if (!$this->connectorBuilder)
		{
			return $this->createConnectorBuilder();
		}
		
		return $this->connectorBuilder;
	}
	
	public function getQueueLoader(string $name): IQueueObjectLoader
	{
		if (!$this->loaderBuilder)
		{
			return $this->createLoaderBuilder()->getRemoteLoader($name);
		}
		
		return $this->loaderBuilder->getRemoteLoader($name);
	}
	
	/**
	 * @param string|IDecoratorBuilder[] $builders
	 */
	public function addConnectorBuilder(...$builders): IDeepQueueConfig
	{
		foreach ($builders as $builder)
		{
			if (is_array($builder))
			{
				$this->addConnectorBuilder(...$builder);
			}
			else if (is_string($builder))
			{
				$this->connectorBuilder->addBuilder(new ClassNameBuilder($builder));
			}
			else if ($builder instanceof IDecoratorBuilder)
			{
				$this->connectorBuilder->addBuilder($builder); 
			}
			else
			{
				throw new InvalidUsageException('Parameter must be string, array or IConnectorBuilder instance!');
			}
		}
		
		return $this;
	}
	
	/**
	 * @param string|ILoaderDecoratorBuilder[] $builders
	 */
	public function addLoaderBuilder(...$builders): IDeepQueueConfig
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
		
		return $this;
	}
	
	/**
	 * @param int $policy See QueueLoaderPolicy const
	 * @see QueueLoaderPolicy
	 */
	public function setQueueNotExistsPolicy(int $policy): IDeepQueueConfig
	{
		$this->queueNotExistsPolicy = $policy;
		return $this;
	}
	
	public function notExistsPolicy(): int
	{
		return $this->queueNotExistsPolicy;
	}
	
	public function remote(): IRemotePlugin
	{
		return $this->remotePlugin;
	}
	
	public function manager(): IManagerPlugin
	{
		return $this->managerPlugin;
	}
	
	public function setRemotePlugin(IRemotePlugin $plugin): IDeepQueueConfig
	{
		$this->remotePlugin = $plugin;
		return $this;
	}
	
	public function setManagerPlugin(IManagerPlugin $plugin): IDeepQueueConfig
	{
		$this->managerPlugin = $plugin;
		return $this;
	}
}