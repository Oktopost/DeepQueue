<?php
namespace DeepQueue\Module\Loader;


use DeepQueue\Base\IQueueObject;
use DeepQueue\Base\Loader\IQueueObjectLoader;
use DeepQueue\Base\Loader\Remote\IRemoteQueueObjectLoader;
use DeepQueue\Enums\QueueLoaderPolicy;
use DeepQueue\Exceptions\QueueNotExistsException;


class QueueObjectLoader implements IQueueObjectLoader
{
	private $name;
	private $queueNotExistsPolicy;
	
	/** @var IRemoteQueueObjectLoader */
	private $remoteLoader;

	
	private function create(): IQueueObject
	{
		return $this->remoteLoader->create($this->name);
	}
	
	private function canCreateNew(): bool
	{
		return $this->queueNotExistsPolicy == QueueLoaderPolicy::CREATE_NEW;	
	}
	
	
	public function __construct(IRemoteQueueObjectLoader $remoteLoader, $name, $queueNotExistsPolicy)
	{
		$this->remoteLoader = $remoteLoader;
		
		$this->name = $name;
		$this->queueNotExistsPolicy = $queueNotExistsPolicy;
	}


	public function load(): ?IQueueObject
	{
		$queueObject = $this->remoteLoader->load($this->name);
		
		if (!$queueObject && $this->canCreateNew())
		{
			$queueObject = $this->create();
		}
		
		return $queueObject;
	}
	
	/**
	 * Exception is thrown if queue does not exist and can't be created.
	 * @return IQueueObject
	 */
	public function require(): IQueueObject
	{
		$queueObject = $this->remoteLoader->load($this->name);
		
		if (!$queueObject && $this->canCreateNew())
		{
			$queueObject = $this->create();
		}
		
		if (!$queueObject)
		{
			throw new QueueNotExistsException($this->name);
		}
		
		return $queueObject;
	}
}