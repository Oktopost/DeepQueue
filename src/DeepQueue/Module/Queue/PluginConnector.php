<?php
namespace DeepQueue\Module\Queue;


use DeepQueue\Base\Queue\IQueue;
use DeepQueue\Base\Queue\Connector\IConnector;
use DeepQueue\Base\Plugins\IRemotePlugin;

class PluginConnector implements IConnector
{
	use \DeepQueue\Base\Queue\Connector\TConnector;
	
	
	/** @var IRemotePlugin */
	private $plugin;
	
	
	public function __construct(IRemotePlugin $plugin)
	{
		$this->plugin = $plugin;
	}
	
	
	public function getStream(string $name): IQueue
	{
		$remoteQueue = $this->plugin->getQueue($name);
		return new Queue($remoteQueue);
	}
}