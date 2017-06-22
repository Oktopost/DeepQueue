<?php
namespace DeepQueue\Plugins\InMemoryManager\Queue;


use DeepQueue\Base\IMetaData;
use DeepQueue\Base\IQueueObject;
use DeepQueue\Base\IDeepQueueConfig;
use DeepQueue\Base\Queue\IQueue;
use DeepQueue\Enums\QueueState;

use DeepQueue\Module\Queue\Queue;
use Objection\LiteObject;
use Objection\LiteSetup;


class InMemoryQueue extends LiteObject implements IQueueObject
{
	/** @var IDeepQueueConfig */
	private $deepConfig = null;
	
	
	protected function _setup()
	{
		return [
			'ID'		=> LiteSetup::createString(),
			'Name'		=> LiteSetup::createString(),
			'State'		=> LiteSetup::createString(QueueState::PAUSED),
			'Config'	=> LiteSetup::createInstanceOf(InMemoryQueueConfig::class)
		];
	}

	public function setDeepConfig(IDeepQueueConfig $config): void
	{
		$this->deepConfig = $config;
	}
	
	public function getStream(): IQueue
	{
		return new Queue($this->deepConfig->getConnectorProvider()->getRemoteQueue($this->Name));
	}

	public function getMetaData(): IMetaData
	{
		return $this->deepConfig->connector()->getMetaData($this);
	}
}