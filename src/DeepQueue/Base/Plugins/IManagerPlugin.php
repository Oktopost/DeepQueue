<?php
namespace DeepQueue\Base\Plugins;


use DeepQueue\Base\Loader\IRemoteQueueObjectLoader;
use DeepQueue\Base\Plugins\ManagerElements\IQueueDAO;


interface IManagerPlugin extends IRemoteQueueObjectLoader
{
	public function getQueueDAO(): IQueueDAO;
}