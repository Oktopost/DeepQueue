<?php
namespace DeepQueue\Plugins\Managers\MemoryCacheManager;


use DeepQueue\Base\IQueueObject;
use DeepQueue\Base\IDeepQueueConfig;
use DeepQueue\Base\Plugins\IManagerPlugin;
use DeepQueue\Base\Plugins\ICacheableManager;


class MemoryCacheManager implements ICacheableManager
{
	private $ttl;
	
	/** @var IManagerPlugin */
	private $parent;
	
	private $cache = [];
	
	
	public function __construct(IManagerPlugin $parent, float $ttl = 1.0)
	{
		$this->parent	= $parent; 
		$this->ttl		= $ttl;
	}
	

	public function setTTL(int $seconds): void
	{
		if ($this->parent instanceof ICacheableManager)
		{
			$this->parent->setTTL($seconds);
		}
	}

	public function setDeepConfig(IDeepQueueConfig $config): void
	{
		$this->parent->setDeepConfig($config);
	}

	public function create(IQueueObject $object): IQueueObject
	{
		return $this->parent->create($object);
	}

	public function update(IQueueObject $object): IQueueObject
	{
		$this->cache = [];
		return $this->parent->update($object);
	}

	/**
	 * @param string|IQueueObject $object Id or the object itself.
	 */
	public function delete($object): void
	{
		$this->cache = [];
		$this->parent->delete($object);
	}

	public function load(string $name, bool $canCreate = false): ?IQueueObject
	{
		$cachedData = $this->cache[$name] ?? null;
		
		if ($cachedData)
		{
			$now = microtime(true);
			$cacheTime = $cachedData[1];
			
			if ($cacheTime + $this->ttl >= $now)
			{
				return $cachedData[0];
			}
		}
		
		$result = $this->parent->load($name, $canCreate);
		
		if ($result)
		{
			$this->cache[$name] = [$result, microtime(true)];
		}
		
		return $result;
	}

	public function loadById(string $id): ?IQueueObject
	{
		return $this->parent->loadById($id);
	}

	/**
	 * @return IQueueObject[]|array
	 */
	public function loadAll(): array
	{
		return $this->parent->loadAll();
	}

	public function flushCache(): void
	{
		$this->cache = [];
		
		if ($this->parent instanceof ICacheableManager)
		{
			$this->parent->flushCache();
		}
	}
}