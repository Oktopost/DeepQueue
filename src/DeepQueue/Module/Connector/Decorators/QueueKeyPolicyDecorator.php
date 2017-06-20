<?php
namespace DeepQueue\Module\Connector\Decorators;


use DeepQueue\Enums\Policy;
use DeepQueue\Module\Ids\TimeBasedRandomGenerator;
use DeepQueue\Payload;
use DeepQueue\Base\Connector\Decorator\IRemoteQueueDecorator;


class QueueKeyPolicyDecorator implements IRemoteQueueDecorator
{
	use \DeepQueue\Module\Connector\Decorators\Base\TDequeueDecorator;

	/** @var TimeBasedRandomGenerator */
	private $idGenerator = null;
	
	
	private function getQueueUniqueKeyPolicy(): int 
	{
		return $this->getQueue()->Config->UniqueKeyPolicy;
	}
	
	private function getId()
	{
		if (!$this->idGenerator)
		{
			$this->idGenerator = new TimeBasedRandomGenerator();
		}
		
		return $this->idGenerator->get();
	}
	
	private function applyAllowedPolicy(Payload $payload): Payload
	{
		if (!$payload->Key)
		{
			$payload->Key = $this->getId();
		}
		
		return $payload;
	}
	
	private function applyRequiredPolicy(Payload $payload): Payload
	{
		$payload->Key = $this->getId();
	}
	
	private function applyForbiddenPolicy(Payload $payload): Payload
	{
		if (!$payload->Key)
		{
			
		}
		
		return $payload;
	}
	
	private function applyKeyPolicy(Payload $payload): Payload
	{
		switch ($this->getQueueUniqueKeyPolicy())
		{
			case Policy::ALLOWED:
				$payload = $this->applyAllowedPolicy($payload);
				break;
				
			case Policy::REQUIRED:
				$payload = $this->applyRequiredPolicy($payload);
				break;
				
			case Policy::FORBIDDEN:
				$payload = $this->applyForbiddenPolicy($payload);
				break;
		}
		
		return $payload;
	}
	
	
	public function enqueue(array $payload): array
	{
		foreach ($payload as $item) 
		{
			$this->applyKeyPolicy($item);
		}
		
		return $payload;
	}
}