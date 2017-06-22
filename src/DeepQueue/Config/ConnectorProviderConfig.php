<?php
namespace DeepQueue\Config;


use DeepQueue\Scope;
use DeepQueue\Base\Plugins\IConnectorPlugin;
use DeepQueue\Base\Loader\IQueueLoaderBuilder;
use DeepQueue\Base\Config\IConnectorProviderConfig;
use DeepQueue\Base\Connector\IConnectorBuilder;
use DeepQueue\Base\Connector\IConnectorProvider;
use DeepQueue\Base\Connector\Decorator\IDecoratorBuilder;
use DeepQueue\Module\Connector\Builder\ClassNameBuilder;
use DeepQueue\Module\Connector\Decorators\QueueStateDecorator;
use DeepQueue\Exceptions\InvalidUsageException;


class ConnectorProviderConfig implements IConnectorProviderConfig
{
	/** @var IConnectorBuilder */
	private $connectorBuilder;
	
	/** @var IConnectorPlugin */
	private $connector;
	
	/** @var IQueueLoaderBuilder  */
	private $loaderBuilder;
	
	private function createConnectorBuilder(): IConnectorBuilder
	{
		$this->connectorBuilder = Scope::skeleton(IConnectorBuilder::class);

		$this->connectorBuilder->setRemoteProvider($this->connector);
		$this->connectorBuilder->setLoaderBuilder($this->loaderBuilder);
		
		$this->addConnectorBuilder(
			QueueStateDecorator::class
		);
		
		return $this->connectorBuilder;
	}

	
	public function setConnector(IConnectorPlugin $connector): IConnectorProviderConfig
	{
		$this->connector = $connector;
		
		return $this;
	}
	
	public function setLoaderBuilder(IQueueLoaderBuilder $loaderBuilder): IConnectorProviderConfig
	{
		$this->loaderBuilder = $loaderBuilder;
		
		return $this;
	}
	
	/**
	 * @param string|IDecoratorBuilder[] $builders
	 */
	public function addConnectorBuilder(...$builders): void
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
	}
	
	public function getConnectorProvider(): IConnectorProvider
	{
		if (!$this->connectorBuilder)
		{
			return $this->createConnectorBuilder();
		}
		
		return $this->connectorBuilder;
	}
}