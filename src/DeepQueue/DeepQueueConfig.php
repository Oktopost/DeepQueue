<?php
namespace DeepQueue;


use DeepQueue\Base\IDeepQueueConfig;
use DeepQueue\Base\Utils\IDecoratorBuilder;
use DeepQueue\Base\Loader\IQueueObjectLoader;
use DeepQueue\Base\Loader\IQueueLoaderBuilder;
use DeepQueue\Base\Config\IQueueLoaderConfig;
use DeepQueue\Base\Config\IConnectorProviderConfig;
use DeepQueue\Base\Plugins\IConnectorPlugin;
use DeepQueue\Base\Plugins\IManagerPlugin;
use DeepQueue\Base\Connector\IConnectorProvider;
use DeepQueue\Enums\QueueLoaderPolicy;
use DeepQueue\Plugins\Logger\Logger;
use DeepQueue\Plugins\Logger\Base\ILogger;
use DeepQueue\Plugins\Logger\Base\ILogProvider;

use Serialization\Base\ISerializer;
use Serialization\Base\Json\IJsonDataConstructor;


class DeepQueueConfig implements IDeepQueueConfig 
{
	/** @var IConnectorProviderConfig */
	private $connectorConfig;

	/** @var IQueueLoaderConfig */
	private $loaderConfig;

	/** @var IConnectorPlugin */
	private $connectorPlugin = null;

	/** @var IManagerPlugin */
	private $managerPlugin = null;

	/** @var ISerializer|IJsonDataConstructor */
	private $payloadDataSerializer = null;
	
	/** @var int */
	private $queueNotExistsPolicy = QueueLoaderPolicy::FORBIDDEN;
	
	/** @var ILogger */
	private $loggerPlugin;


	public function __construct()
	{
		$this->connectorConfig = Scope::skeleton(IConnectorProviderConfig::class);
		$this->loaderConfig = Scope::skeleton(IQueueLoaderConfig::class);
		
		$this->loggerPlugin = Scope::skeleton(ILogger::class);
	}


	public function getConnectorProvider(): IConnectorProvider
	{
		return $this->connectorConfig
			->setConnector($this->connector())
			->setLoaderBuilder($this->getLoaderBuilder())
			->getConnectorProvider();
	}

	public function getLoaderBuilder(): IQueueLoaderBuilder
	{
		return $this->loaderConfig
			->setQueueNotExistsPolicy($this->queueNotExistsPolicy)
			->setManager($this->manager())
			->getLoaderBuilder();
	}

	public function getQueueLoader(string $name): IQueueObjectLoader
	{
		return $this->getLoaderBuilder()->getRemoteLoader($name);
	}
	
	/**
	 * @param string|IDecoratorBuilder[] $builders
	 */
	public function addConnectorBuilder(...$builders): IDeepQueueConfig
	{
		$this->connectorConfig
			->setConnector($this->connector())
			->setLoaderBuilder($this->getLoaderBuilder())
			->addConnectorBuilder($builders);
		
		return $this;
	}
	
	/**
	 * @param string|IDecoratorBuilder[] $builders
	 */
	public function addLoaderBuilder(...$builders): IDeepQueueConfig
	{
		$this->loaderConfig
			->setQueueNotExistsPolicy($this->queueNotExistsPolicy)
			->setManager($this->manager())
			->addLoaderBuilder($builders);
		
		return $this;
	}
	
	public function addLogProvider(ILogProvider $provider): IDeepQueueConfig
	{
		$this->logger()->addProvider($provider);
		return $this;
	}

	public function setSerializer(ISerializer $serializer): IDeepQueueConfig
	{
		$this->payloadDataSerializer = $serializer;
		return $this;
	}
	
	public function setConnectorPlugin(IConnectorPlugin $plugin): IDeepQueueConfig
	{
		$this->connectorPlugin = $plugin;
		$this->connectorPlugin->setDeepConfig($this);
		return $this;
	}
	
	public function setManagerPlugin(IManagerPlugin $plugin): IDeepQueueConfig
	{
		$this->managerPlugin = $plugin;
		$this->managerPlugin->setDeepConfig($this);
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
	
	public function serializer(): ISerializer
	{
		return $this->payloadDataSerializer;
	}
	
	public function notExistsPolicy(): int
	{
		return $this->queueNotExistsPolicy;
	}
	
	public function connector(): IConnectorPlugin
	{
		return $this->connectorPlugin;
	}
	
	public function manager(): IManagerPlugin
	{
		return $this->managerPlugin;
	}

	public function logger(): ILogger
	{
		return $this->loggerPlugin;
	}
}