<?php
namespace DeepQueue\Base\Plugins;


use DeepQueue\Base\IQueueObject;
use DeepQueue\Base\Plugins\ManagerElements\IQueueDAO;


interface IQueueLoaderPlugin
{
	public function loadQueue(IQueueDAO $dao, string $name): ?IQueueObject
}