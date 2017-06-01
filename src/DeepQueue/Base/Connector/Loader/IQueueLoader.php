<?php
namespace DeepQueue\Base\Connector\Loader;


use DeepQueue\Base\IQueueObject;


interface IQueueLoader
{
	public function load(): ?IQueueObject;
	
	/**
	 * Exception is thrown if queue does not exist.
	 * @return IQueueObject
	 */
	public function require(): IQueueObject;
}