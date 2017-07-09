<?php
namespace DeepQueue\Plugins\Managers\CachedManager;


use DeepQueue\Base\IQueueObject;
use DeepQueue\Base\IDeepQueueConfig;
use DeepQueue\Base\Plugins\ICacheableManager;
use DeepQueue\Base\Plugins\IManagerPlugin;
use DeepQueue\Plugins\Managers\CachedManager\Base\ICachedManager;


class CachedManager implements ICachedManager
{
	/** @var  IManagerPlugin */
	private $main;
	
	/** @var  IManagerPlugin */
	private $cache;
	
	
	public function __construct(IManagerPlugin $main, IManagerPlugin $cache)
	{
		$this->main = $main;
		$this->cache = $cache;
	}

	
	public function setTTL(int $seconds): void
	{
		if ($this->cache instanceof ICacheableManager)
		{
			$this->cache->setTTL($seconds);
		}
	}

	public function setDeepConfig(IDeepQueueConfig $config): void
	{
		$this->main->setDeepConfig($config);
		$this->cache->setDeepConfig($config);
	}

	public function create(IQueueObject $object): IQueueObject
	{
		return $this->main->create($object);
	}

	public function update(IQueueObject $object): IQueueObject
	{
		$this->cache->delete($object);
		
		return $this->main->update($object);
	}

	public function delete($object): void
	{
		$this->cache->delete($object);
		$this->main->delete($object);
	}

	public function load(string $name, bool $canCreate = false): ?IQueueObject
	{
		$queueObject = $this->cache->load($name);
		
		if (!$queueObject)
		{
			$queueObject = $this->main->load($name, $canCreate);
			
			if ($queueObject)
			{
				$this->cache->create($queueObject);
			}
		}

		return $queueObject ?: null;
	}

	public function loadById(string $id): ?IQueueObject
	{
		return $this->main->loadById($id);
	}

	public function loadAll(): array
	{
		return $this->main->loadAll();
	}
}