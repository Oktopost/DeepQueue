<?php
namespace DeepQueue\Base\Helpers;


use DeepQueue\Base\Queue\Connector\IConnector;
use DeepQueue\Base\Plugins\IRemotePlugin;
use DeepQueue\Base\Plugins\ManagerElements\IQueueDAO;


/**
 * @skeleton
 */
interface IQueueBuilder extends IConnector
{
	public function setRemotePlugin(IRemotePlugin $plugin);
	public function setQueueDAO(IQueueDAO $dao);
}