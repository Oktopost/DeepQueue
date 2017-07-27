<?php
namespace DeepQueue\Plugins\Connectors\RedisConnector\Manager;


use DeepQueue\Base\Plugins\ConnectorElements\IQueueManagerDAO;
use DeepQueue\Plugins\Connectors\BaseQueueManager;
use DeepQueue\Plugins\Connectors\RedisConnector\Base\IRedisQueueManager;


class RedisQueueManager extends BaseQueueManager implements IRedisQueueManager
{
	public function __construct(IQueueManagerDAO $dao)
	{
		$this->setDAO($dao);
	}
}