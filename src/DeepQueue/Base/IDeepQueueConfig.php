<?php
namespace DeepQueue\Base;


use DeepQueue\Base\Connector\IConnectorProvider;
use DeepQueue\Base\Loader\IQueueObjectLoader;
use DeepQueue\Base\Plugins\IConnectorPlugin;
use DeepQueue\Base\Plugins\IManagerPlugin;
use DeepQueue\Base\Data\IPayloadConverter;
use DeepQueue\Base\Loader\Decorator\ILoaderDecoratorBuilder;

use Serialization\Base\ISerializer;


interface IDeepQueueConfig
{
	/**
	 * @param string[]|array[]|IDeepQueueConfig[] $builder
	 */
	public function addConnectorBuilder(...$builders): IDeepQueueConfig;
	
	/**
	 * @param string|ILoaderDecoratorBuilder[] $builder
	 */
	public function addLoaderBuilder(...$builders): IDeepQueueConfig;

	/**
	 * @param int $policy See QueueLoaderPolicy const
	 * @see QueueLoaderPolicy
	 */
	public function setQueueNotExistsPolicy(int $policy): IDeepQueueConfig;

	public function getConnectorProvider(): IConnectorProvider;
	public function getQueueLoader(string $name): IQueueObjectLoader;
	
	public function setPayloadDataSerializer(ISerializer $serializer);	
	public function setConnectorPlugin(IConnectorPlugin $plugin): IDeepQueueConfig;
	public function setManagerPlugin(IManagerPlugin $plugin): IDeepQueueConfig;
	
	public function notExistsPolicy(): int;
	public function connector(): IConnectorPlugin;
	public function manager(): IManagerPlugin;
	public function converter(): IPayloadConverter;
}