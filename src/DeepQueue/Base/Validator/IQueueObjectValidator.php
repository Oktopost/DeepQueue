<?php
namespace DeepQueue\Base\Validator;


use DeepQueue\Base\IQueueObject;


interface IQueueObjectValidator
{
	public function validate(IQueueObject $queueObject): void;
}