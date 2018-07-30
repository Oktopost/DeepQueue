<?php
namespace DeepQueue\Plugins\Managers\SafeManager;


use DeepQueue\Base\IQueueObject;
use DeepQueue\Base\IDeepQueueConfig;
use DeepQueue\Base\Plugins\IManagerPlugin;


class SafeManager implements IManagerPlugin
{
	/** @var callable|null */
	private $onError;
	
	/** @var IManagerPlugin */
	private $parent;
	
	
	private function handleError(\Throwable $t): void
	{
		if (!$this->onError)
			return;
		
		$onError = $this->onError;
		$onError($t);
	}
	
	
	public function __construct(IManagerPlugin $parent, ?callable $onError = null)
	{
		$this->parent = $parent;
		$this->onError = $onError;
	}
	
	
	public function setDeepConfig(IDeepQueueConfig $config): void
	{
		try
		{
			$this->parent->setDeepConfig($config);
		}
		catch (\Throwable $t)
		{
			$this->handleError($t);
		}
	}
	
	public function create(IQueueObject $object): IQueueObject
	{
		try
		{
			return $this->parent->create($object);
		}
		catch (\Throwable $t)
		{
			$this->handleError($t);
			return $object;
		}
	}
	
	public function update(IQueueObject $object): IQueueObject
	{
		try
		{
			return $this->parent->update($object);
		}
		catch (\Throwable $t)
		{
			$this->handleError($t);
			return $object;
		}
	}
	
	/**
	 * @param string|IQueueObject $object Id or the object itself.
	 */
	public function delete($object): void
	{
		try
		{
			$this->parent->delete($object);
		}
		catch (\Throwable $t)
		{
			$this->handleError($t);
		}
	}
	
	public function load(string $name, bool $canCreate = false): ?IQueueObject
	{
		try
		{
			return $this->parent->load($name, $canCreate);
		}
		catch (\Throwable $t)
		{
			$this->handleError($t);
			return null;
		}
	}
	
	public function loadById(string $id): ?IQueueObject
	{
		try
		{
			return $this->parent->loadById($id);
		}
		catch (\Throwable $t)
		{
			$this->handleError($t);
			return null;
		}
	}
	
	/**
	 * @return IQueueObject[]|array
	 */
	public function loadAll(): array
	{
		try
		{
			return $this->parent->loadAll();
		}
		catch (\Throwable $t)
		{
			$this->handleError($t);
			return [];
		}
	}
}