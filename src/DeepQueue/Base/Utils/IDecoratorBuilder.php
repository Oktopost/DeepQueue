<?php
namespace DeepQueue\Base\Utils;


use DeepQueue\Base\Loader\Decorator\IQueueLoaderDecorator;
use DeepQueue\Base\Connector\Decorator\IRemoteQueueDecorator;


interface IDecoratorBuilder
{
	public function buildForConnector(): IRemoteQueueDecorator;
	public function buildForLoader(): IQueueLoaderDecorator;
}