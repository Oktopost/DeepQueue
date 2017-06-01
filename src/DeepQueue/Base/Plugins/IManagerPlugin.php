<?php
namespace DeepQueue\Base\Plugins;


use DeepQueue\Base\Plugins\ManagerElements\IQueueDAO;


interface IManagerPlugin
{
	public function getQueueDAO(): IQueueDAO;
}