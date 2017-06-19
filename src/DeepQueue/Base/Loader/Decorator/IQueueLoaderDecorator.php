<?php
namespace DeepQueue\Base\Loader\Decorator;


use DeepQueue\Base\Loader\IQueueObjectLoader;


interface IQueueLoaderDecorator extends IQueueObjectLoader
{
	public function setChildLoader(IQueueObjectLoader $loader): void;
}