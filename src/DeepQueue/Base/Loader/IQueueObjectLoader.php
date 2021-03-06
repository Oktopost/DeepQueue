<?php
namespace DeepQueue\Base\Loader;


use DeepQueue\Base\IQueueObject;


interface IQueueObjectLoader
{
	public function load(): ?IQueueObject;
	
	/**
	 * Exception is thrown if queue does not exist.
	 * @return IQueueObject
	 */
	public function require(): IQueueObject;
}