<?php
namespace DeepQueue\Base\Validator;


use DeepQueue\Base\IQueueObject;


interface IPayloadValidator
{
	public function validate(array $payload, IQueueObject $queue): void;
}