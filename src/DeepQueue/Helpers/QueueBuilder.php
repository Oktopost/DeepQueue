<?php
namespace DeepQueue\Helpers;


use DeepQueue\Scope;
use DeepQueue\Base\Queue\IQueue;
use DeepQueue\Base\Helpers\IQueueBuilder;
use DeepQueue\Base\Plugins\IRemotePlugin;
use DeepQueue\Base\Plugins\ManagerElements\IQueueDAO;
use DeepQueue\Base\Validator\IValidator;
use DeepQueue\Module\Queue\Queue;
use DeepQueue\Module\Validator\Loader;


class QueueBuilder implements IQueueBuilder
{
	use \DeepQueue\Base\Queue\Connector\TConnector;
	
	
	/** @var IRemotePlugin */
	private $plugin;
	
	/** @var IQueueDAO */
	private $dao;
	
	
	public function getStream(string $name): IQueue
	{
		$queue = $this->plugin->getQueue($name);
		
		/** @var IValidator $validator */
		$validator = Scope::skeleton(IValidator::class);
		$validator->setLoader(new Loader($name, $this->dao));
		$validator->setRemoteEnqueue($queue);
		
		return new Queue($queue, $validator);
	}
	
	
	public function setRemotePlugin(IRemotePlugin $plugin)
	{
		$this->plugin = $plugin;
	}
	
	public function setQueueDAO(IQueueDAO $dao)
	{
		$this->dao = $dao;
	}
}