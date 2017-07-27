<?php
namespace DeepQueue\Plugins\Connectors\MySQLConnector\Manager;


use DeepQueue\Base\Plugins\ConnectorElements\IQueueManagerDAO;
use DeepQueue\Plugins\Connectors\BaseQueueManager;
use DeepQueue\Plugins\Connectors\MySQLConnector\Base\IMySQLQueueManager;


class MySQLQueueManager extends BaseQueueManager implements IMySQLQueueManager
{
	public function __construct(IQueueManagerDAO $dao)
	{
		$this->setDAO($dao);
	}
}