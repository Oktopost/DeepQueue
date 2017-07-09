<?php
namespace DeepQueue\Config;


use DeepQueue\Scope;
use DeepQueue\Base\Plugins\IManagerPlugin;
use DeepQueue\Base\Utils\IDecoratorBuilder;
use DeepQueue\Base\Config\IQueueLoaderConfig;
use DeepQueue\Base\Loader\IQueueObjectLoader;
use DeepQueue\Base\Loader\IQueueLoaderBuilder;
use DeepQueue\Utils\ClassNameBuilder;
use DeepQueue\Enums\QueueLoaderPolicy;
use DeepQueue\Module\Loader\Decorators\CachedLoaderDecorator;
use DeepQueue\Exceptions\InvalidUsageException;


class QueueLoaderConfig implements IQueueLoaderConfig
{
	/** @var IQueueLoaderBuilder */
	private $loaderBuilder;
	
	/** @var IManagerPlugin */
	private $manager = null;
	
	private $queueNotExistsPolicy = QueueLoaderPolicy::FORBIDDEN;
	
	
	private function checkConfiguration(): void
	{
		if (!($this->manager instanceof IManagerPlugin))
		{
			throw new InvalidUsageException('Manager plugin must be setted up');
		}
	}
	
	private function createLoaderBuilder(): IQueueLoaderBuilder
	{
		$this->checkConfiguration();
		
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
	 * @param string|IDecoratorBuilder[] $builders
	 */
	public function addLoaderBuilder(...$builders): IQueueLoaderConfig
	{
		if (!$this->loaderBuilder)
		{
			$this->createLoaderBuilder();
		}
		
		foreach ($builders as $builder)
		{
			if (is_array($builder))
			{
				$this->addLoaderBuilder(...$builder);
			}
			else if (is_string($builder))
			{
				$this->loaderBuilder->addBuilder(new ClassNameBuilder($builder));
			}
			else if ($builder instanceof IDecoratorBuilder)
			{
				$this->loaderBuilder->addBuilder($builder); 
			}
			else
			{
				throw new InvalidUsageException('Parameter must be string, array or IDecoratorBuilder instance!');
			}
		}
		
		return $this;
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