<?php
namespace DeepQueue\Module\Validator;


use DeepQueue\Payload;
use DeepQueue\Enums\Policy;
use DeepQueue\Base\IQueueObject;
use DeepQueue\Base\Validator\IDelayValidator;
use DeepQueue\Exceptions\ValidationErrorCode;
use DeepQueue\Exceptions\ValidationException;


class DelayValidator implements IDelayValidator
{
	private function checkMinAndMaxDelay(Payload $payload, IQueueObject $queue): void
	{
		if ($payload->Delay > $queue->Config->MaximalDelay)
		{
			$payload->Delay = $queue->Config->MaximalDelay;
		}
		
		if ($payload->Delay < $queue->Config->MinimalDelay)
		{
			$payload->Delay = $queue->Config->MinimalDelay;
		}
	}
	
	private function applyIgnored(array $payload): void
	{
		/** @var Payload $item */
		foreach ($payload as $item)
		{
			$item->Delay = 0;
		}
	}
	
	private function checkAllowedPolicy(array $payload, IQueueObject $queue): void
	{
		/** @var Payload $item */
		foreach ($payload as $item)
		{
			if ($item->Delay > 0)
			{
				$this->checkMinAndMaxDelay($item, $queue);
			}
		}
	}
	
	private function checkRequiredPolicy(array $payload, IQueueObject $queue): void
	{
		/** @var Payload $item */
		foreach ($payload as $item)
		{
			if (($item->Delay <= 0) && ($queue->Config->DefaultDelay <= 0))
			{
				throw new ValidationException(ValidationErrorCode::DELAY_REQUIRED, 
					'Non-zero delay in payload is required for this Queue');
			}
			
			if ($item->Delay <= 0)
			{
				$item->Delay = $queue->Config->DefaultDelay;
			}
			else
			{
				$this->checkMinAndMaxDelay($item, $queue);
			}
			
		}
	}
	
	private function checkForbiddenPolicy(array $payload): void
	{
		/** @var Payload $item */
		foreach ($payload as $item)
		{
			if ($item->Delay > 0)
			{
				throw new ValidationException(ValidationErrorCode::DELAY_FORBIDDEN, 
					'Non-zero delay in payload is forbidden for this Queue');
			}
		}
	}
	
	public function validate(array $payload, IQueueObject $queue): void
	{
		switch ($queue->Config->DelayPolicy)
		{
			case Policy::IGNORED:
				$this->applyIgnored($payload);
				return;
				
			case Policy::ALLOWED:
				$this->checkAllowedPolicy($payload, $queue);
				break;
			
			case Policy::REQUIRED:
				$this->checkRequiredPolicy($payload, $queue);
				break;
				
			case Policy::FORBIDDEN:
				$this->checkForbiddenPolicy($payload);
				break;
		}
	}
}