<?php
namespace DeepQueue\Module\Validator;


use DeepQueue\Payload;
use DeepQueue\Enums\Policy;
use DeepQueue\Base\IQueueObject;
use DeepQueue\Base\Validator\IKeyValidator;
use DeepQueue\Exceptions\ValidationErrorCode;
use DeepQueue\Exceptions\ValidationException;


class KeyValidator implements IKeyValidator
{
	private function checkRequiredPolicy(array $payload): void
	{
		/** @var Payload $item */
		foreach ($payload as $item)
		{
			if (!$item->hasKey())
			{
				throw new ValidationException(ValidationErrorCode::KEY_REQUIRED, 
					'Unique key in payload is required for this Queue');
			}
		}
	}
	
	private function checkForbiddenPolicy(array $payload): void
	{
		/** @var Payload $item */
		foreach ($payload as $item)
		{
			if ($item->hasKey())
			{
				throw new ValidationException(ValidationErrorCode::KEY_FORBIDDEN, 
					'Unique key in payload is forbidden for this Queue');
			}
		}
	}
	
	private function applyIgnored(array $payload): void
	{
		/** @var Payload $item */
		foreach ($payload as $item) 
		{
			$item->Key = null;	
		}
	}
	
	
	public function validate(array $payload, IQueueObject $queue): void
	{
		switch ($queue->Config->UniqueKeyPolicy)
		{
			case Policy::ALLOWED:
				return;
				
			case Policy::IGNORED:
				$this->applyIgnored($payload);
				break;
			
			case Policy::REQUIRED:
				$this->checkRequiredPolicy($payload);
				break;
				
			case Policy::FORBIDDEN:
				$this->checkForbiddenPolicy($payload);
				break;
		}
	}
}