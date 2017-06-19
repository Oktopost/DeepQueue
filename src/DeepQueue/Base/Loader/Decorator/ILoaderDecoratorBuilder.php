<?php
namespace DeepQueue\Base\Loader\Decorator;


interface ILoaderDecoratorBuilder
{
	public function build(): IQueueLoaderDecorator;
}