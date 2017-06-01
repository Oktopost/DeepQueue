<?php
namespace DeepQueue\Module\Validator;


use DeepQueue\Payload;
use DeepQueue\Base\IQueueObject;
use DeepQueue\Base\Queue\Remote\IRemoteEnqueue;
use DeepQueue\Base\Validator\IValidator;
use DeepQueue\Base\Validator\IQueueLoader;


class Validator implements IValidator 
{
	/** @var IQueueLoader */
	private $loader;
	
	/** @var IRemoteEnqueue */
	private $queue;
	
	
	/**
	 * @param IQueueObject $object
	 * @param Payload[] $payload
	 */
	private function validate(IQueueObject $object, array $payload)
	{
		// TODO:
	}
	
	
	public function setLoader(IQueueLoader $loader): void
	{
		$this->loader = $loader;
	}
	
	public function setRemoteEnqueue(IRemoteEnqueue $enqueue): void
	{
		$this->queue = $enqueue;
	}
	
	
	/**
	 * @param Payload[] $payload
	 * @return string[] IDs for each payload
	 */
	public function enqueue(array $payload): array
	{
		$object = $this->loader->load();
		$this->validate($object, $payload);
		return $this->queue->enqueue($payload);
	}
}