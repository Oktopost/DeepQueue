<?php
namespace DeepQueue\Base\Config;


use DeepQueue\Base\Loader\IQueueObjectLoader;
use DeepQueue\Base\Loader\IQueueLoaderBuilder;
use DeepQueue\Base\Plugins\IManagerPlugin;
use DeepQueue\Base\Utils\IDecoratorBuilder;


interface IQueueLoaderConfig
{
	public function setManager(IManagerPlugin $manager): IQueueLoaderConfig;
	public function setQueueNotExistsPolicy(int $queueNotExistsPolicy): IQueueLoaderConfig;

	/**
	 * @param string|IDecoratorBuilder[] $builders
	 */
	public function addLoaderBuilder(...$builders): IQueueLoaderConfig;
	
	public function getQueueLoader(string $name): IQueueObjectLoader;
	public function getLoaderBuilder(): IQueueLoaderBuilder;
}