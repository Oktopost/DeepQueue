<?php
namespace DeepQueue\Config;


use DeepQueue\Scope;
use DeepQueue\Base\Utils\IDecoratorBuilder;
use DeepQueue\Base\Plugins\IConnectorPlugin;
use DeepQueue\Base\Loader\IQueueLoaderBuilder;
use DeepQueue\Base\Config\IConnectorProviderConfig;
use DeepQueue\Base\Connector\IConnectorBuilder;
use DeepQueue\Base\Connector\IConnectorProvider;
use DeepQueue\Module\Connector\Decorators\Validator;
use DeepQueue\Module\Connector\Decorators\QueueStateDecorator;
use DeepQueue\Exceptions\InvalidUsageException;
use DeepQueue\Utils\ClassNameBuilder;


class ConnectorProviderConfig implements IConnectorProviderConfig
{
	/** @var IConnectorBuilder */
	private $connectorBuilder;
	
	/** @var IConnectorPlugin */
	private $connector = null;
	
	/** @var IQueueLoaderBuilder  */
	private $loaderBuilder = null;
	
	
	private function checkConfiguration(): void
	{
		if (!($this->connector instanceof IConnectorPlugin))
		{
			throw new InvalidUsageException('Connector plugin must be setted up');
		}
		
		if(!($this->loaderBuilder instanceof IQueueLoaderBuilder))
		{
			throw new InvalidUsageException('Loader builder must be setted up');
		}
	}
	
	private function createConnectorBuilder(): IConnectorBuilder
	{
		$this->checkConfiguration();
		
		$this->connectorBuilder = Scope::skeleton(IConnectorBuilder::class);

		$this->connectorBuilder->setRemoteProvider($this->connector);
		$this->connectorBuilder->setLoaderBuilder($this->loaderBuilder);
		
		$this->addConnectorBuilder(
			QueueStateDecorator::class,
			Validator::class
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
	public function addConnectorBuilder(...$builders): IConnectorProviderConfig
	{
		if (!$this->connectorBuilder)
		{
			$this->createConnectorBuilder();
		}
		
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
				throw new InvalidUsageException('Parameter must be string, array or IDecoratorBuilder instance!');
			}
		}
		
		return $this;
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