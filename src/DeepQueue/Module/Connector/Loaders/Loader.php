<?php
namespace DeepQueue\Module\Connector;


use DeepQueue\Base\IQueueObject;
use DeepQueue\Base\Plugins\ManagerElements\IQueueDAO;
use DeepQueue\Base\Connector\Loader\IQueueLoader;
use DeepQueue\Exceptions\QueueNotExistsException;


class Loader implements IQueueLoader
{
	private $name;
	
	/** @var IQueueDAO */
	private $dao;
	
	
	public function __construct(string $name, IQueueDAO $dao)
	{
		$this->name = $name;
		$this->dao  = $dao;
	}
	
	
	public function load(): ?IQueueObject
	{
		return $this->dao->loadIfExists($this->name);
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