<?php
namespace DeepQueue\Base\Connector;


use DeepQueue\Base\Plugins\ManagerElements\IQueueDAO;

interface IQueueLoaderBuilder
{
	public function setDao(IQueueDAO $dao);
	public function create(string $name);
}