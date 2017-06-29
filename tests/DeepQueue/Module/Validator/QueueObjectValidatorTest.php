<?php
namespace DeepQueue\Module\Validator;


use DeepQueue\Base\Validator\IQueueObjectValidator;
use DeepQueue\Manager\QueueConfig;
use DeepQueue\Manager\QueueObject;
use DeepQueue\Scope;

use PHPUnit\Framework\TestCase;

use Skeleton\Skeleton;


class QueueObjectValidatorTest extends TestCase
{
	private function getSubject():IQueueObjectValidator
	{
		/** @var Skeleton */
		$skeleton = Scope::skeleton();
		
		return $skeleton->get(IQueueObjectValidator::class);
	}

	/**
	 * @expectedException \DeepQueue\Exceptions\ValidationException
	 */
	public function test_validate_InvalidObject_ThrowException()
	{
		$queueObject = new QueueObject();
		
		$this->getSubject()->validate($queueObject);
		
		self::assertEquals($queueObject->Id, $queueObject->Id);
	}
	
	public function test_validate_ValidObject_NoException()
	{
		$queueObject = new QueueObject();
		$queueObject->Name = 'test';
		$queueObject->Config = new QueueConfig();
		
		$this->getSubject()->validate($queueObject);
		
		self::assertEquals($queueObject->Name, $queueObject->Name);
	}
}