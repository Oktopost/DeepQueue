<?php
namespace DeepQueue\Base\Validator;


use DeepQueue\Base\IQueueObject;


interface IQueueLoader
{
	public function load(): IQueueObject;
}