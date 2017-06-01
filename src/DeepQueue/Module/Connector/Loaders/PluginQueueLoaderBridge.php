<?php
namespace DeepQueue\Module\Connector\Loaders;


use DeepQueue\Base\IQueueObject;
use DeepQueue\Base\Plugins\IQueueLoaderPlugin;
use DeepQueue\Base\Plugins\ManagerElements\IQueueDAO;
use DeepQueue\Base\Connector\Loader\IQueueLoader;
use DeepQueue\Exceptions\QueueNotExistsException;


class PluginQueueLoaderBridge implements IQueueLoader 
{
	private $name;
	
	/** @var  IQueueDAO */
	private $dao;
	
	/** @var IQueueLoaderPlugin */
	private $pluginLoader;
	
	
	public function __construct(IQueueLoaderPlugin $plugin, string $name, IQueueDAO $dao)
	{
		$this->pluginLoader = $plugin;
	}
	
	public function load(): ?IQueueObject
	{
		return $this->pluginLoader->loadQueue($this->dao, $this->name);
	}
	
	/**
	 * Exception is thrown if queue does not exist.
	 * @return IQueueObject
	 */
	public function require (): IQueueObject
	{
		$object = $this->dao->loadIfExists($this->name);
		
		if (!$object)
		{
			throw new QueueNotExistsException($this->name);
		}
		
		return $object;
	}
}