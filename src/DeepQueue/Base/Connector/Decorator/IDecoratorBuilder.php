<?php
namespace DeepQueue\Base\Connector\Decorator;


interface IDecoratorBuilder
{
	public function build(): IRemoteQueueDecorator;
}