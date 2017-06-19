<?php
namespace DeepQueue;


use DeepQueue\Base\IDeepQueueConfig;
use DeepQueue\Base\Plugins\IManagerPlugin;
use DeepQueue\Base\Connector\IConnectorBuilder;
use DeepQueue\Base\Connector\IConnectorProvider;
use DeepQueue\Base\Loader\IQueueLoaderBuilder;
use DeepQueue\Base\Connector\Decorator\IDecoratorBuilder;
use DeepQueue\Base\Plugins\IRemotePlugin;
use DeepQueue\Enums\QueueLoaderPolicy;
use DeepQueue\Module\Connector\Builder\ClassNameBuilder;
use DeepQueue\Module\Connector\Decorators\QueueStateDecorator;

use DeepQueue\Exceptions\InvalidUsageException;


class DeepQueueConfig implements IDeepQueueConfig 
{
	/** @var IConnectorBuilder */
	private $connectorBuilder;
	
	/** @var IQueueLoaderBuilder */
	private $loaderBuilder;
	
	/** @var IRemotePlugin */
	private $remotePlugin;
	
	/** @var IManagerPlugin */
	private $managerPlugin;
	
	/** @var int */
	private $queueNotExistsPolicy;
	
	
	private function createLoaderBuilder(): IQueueLoaderBuilder
	{
		$this->queueNotExistsPolicy = QueueLoaderPolicy::FORBIDDEN;
		
		$this->loaderBuilder = Scope::skeleton(IQueueLoaderBuilder::class);
		
		return $this->loaderBuilder;
	}
	
	private function createConnectorBuilder(string $name): IConnectorBuilder
	{
		$this->connectorBuilder = Scope::skeleton(IConnectorBuilder::class);
		
		$this->connectorBuilder->setLoader(
			$this->createLoaderBuilder()->getRemoteLoader($name, $this->queueNotExistsPolicy));
		
		$this->addConnectorBuilder(
			QueueStateDecorator::class
		);
		
		return $this->connectorBuilder;
	}
	
	
	public function getConnectorProvider(string $name): IConnectorProvider
	{
		return $this->createConnectorBuilder($name);
	}
	
	/**
	 * @param string|IDecoratorBuilder[] $builder
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
	 * @param int $policy See QueueLoaderPolicy const
	 * @see QueueLoaderPolicy
	 */
	public function setQueueNotExistsPolicy(int $policy)
	{
		$this->queueNotExistsPolicy = $policy;
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