<?php
namespace DeepQueue\Module\Validator;


use DeepQueue\Base\IQueueConfig;
use DeepQueue\Base\IQueueObject;
use DeepQueue\Base\Validator\IQueueObjectValidator;
use DeepQueue\Exceptions\ValidationErrorCode;
use DeepQueue\Exceptions\ValidationException;


class QueueObjectValidator implements IQueueObjectValidator
{
	public function validate(IQueueObject $queueObject): void
	{
		if (!$queueObject->Name || !$queueObject->State || !$queueObject->Config instanceof IQueueConfig)
		{
			throw new ValidationException(ValidationErrorCode::INVALID_QUEUE, 
				'Queue object must have name, state and config');
		}
	}
}