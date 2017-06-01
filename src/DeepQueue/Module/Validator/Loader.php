<?php
namespace DeepQueue\Module\Validator;


use DeepQueue\Base\IQueueObject;
use DeepQueue\Base\Plugins\ManagerElements\IQueueDAO;
use DeepQueue\Base\Validator\IQueueLoader;


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
	
	
	public function load(): IQueueObject
	{
		return $this->dao->load($this->name);
	}
}