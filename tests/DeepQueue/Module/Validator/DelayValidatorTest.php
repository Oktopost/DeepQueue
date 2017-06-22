<?php
namespace DeepQueue;


use DeepQueue\Base\IQueueObject;
use DeepQueue\Enums\Policy;
use DeepQueue\Manager\QueueConfig;
use DeepQueue\Manager\QueueObject;
use DeepQueue\Module\Validator\DelayValidator;
use PHPUnit\Framework\TestCase;


class DelayValidatorTest extends TestCase
{
	private function runSubject(array $payload, IQueueObject $queueObject)
	{
		(new DelayValidator())->validate($payload, $queueObject);
	}
	
	
	public function test_Validate_Ignored()
	{
		$queueObject = new QueueObject();
		$queueObjectConfig = new QueueConfig();
		$queueObjectConfig->DelayPolicy = Policy::IGNORED;
		$queueObjectConfig->MaximalDelay = 10;
		$queueObject->Config = $queueObjectConfig;
		
		
		$payload = new Payload();
		$payload->Delay = 5;
		
		$this->runSubject([$payload], $queueObject);
		
		self::assertEquals($payload->Delay, 0);
	}
	
	public function test_Validate_Allowed()
	{
		$queueObject = new QueueObject();
		$queueObjectConfig = new QueueConfig();
		$queueObjectConfig->DelayPolicy = Policy::ALLOWED;
		$queueObjectConfig->MaximalDelay = 10;
		$queueObject->Config = $queueObjectConfig;
		
		
		$payload = new Payload();
		$payload->Delay = 5;
		
		$payload2 = new Payload();
		$payload2->Delay = 0;
		
		$this->runSubject([$payload, $payload2], $queueObject);
		
		self::assertEquals(5, $payload->Delay);
		self::assertEquals(0, $payload2->Delay);
	}
	
	public function test_Validate_Forbidden_NoDelay()
	{
		$queueObject = new QueueObject();
		$queueObjectConfig = new QueueConfig();
		$queueObjectConfig->DelayPolicy = Policy::FORBIDDEN;
		$queueObject->Config = $queueObjectConfig;
		
		
		$payload = new Payload();
		$payload->Delay = 0;
		
		$payload2 = new Payload();
		$payload2->Delay = 0;
		
		$this->runSubject([$payload, $payload2], $queueObject);
		
		self::assertEquals(0, $payload->Delay);
		self::assertEquals(0, $payload2->Delay);
	}

	/**
	 * @expectedException \DeepQueue\Exceptions\ValidationException
	 */
	public function test_Validate_Forbidden_WithDelay()
	{
		$queueObject = new QueueObject();
		$queueObjectConfig = new QueueConfig();
		$queueObjectConfig->DelayPolicy = Policy::FORBIDDEN;
		$queueObject->Config = $queueObjectConfig;
		
		
		$payload = new Payload();
		$payload->Delay = 5;
		
		$payload2 = new Payload();
		$payload2->Delay = 0;
		
		$this->runSubject([$payload, $payload2], $queueObject);
		
		self::assertEquals(0, $payload->Delay);
		self::assertEquals(0, $payload2->Delay);
	}
	
	public function test_Validate_Required_WithDelay()
	{
		$queueObject = new QueueObject();
		$queueObjectConfig = new QueueConfig();
		$queueObjectConfig->DelayPolicy = Policy::REQUIRED;
		$queueObjectConfig->MaximalDelay = 10;
		$queueObject->Config = $queueObjectConfig;
		
		
		$payload = new Payload();
		$payload->Delay = 1;
		
		$payload2 = new Payload();
		$payload2->Delay = 2;
		
		$this->runSubject([$payload, $payload2], $queueObject);
		
		self::assertEquals(1, $payload->Delay);
		self::assertEquals(2, $payload2->Delay);
	}
	
	/**
	 * @expectedException \DeepQueue\Exceptions\ValidationException
	 */
	public function test_Validate_Required_NoDelay()
	{
		$queueObject = new QueueObject();
		$queueObjectConfig = new QueueConfig();
		$queueObjectConfig->DelayPolicy = Policy::REQUIRED;
		$queueObjectConfig->MaximalDelay = 10;
		$queueObject->Config = $queueObjectConfig;
		
		
		$payload = new Payload();
		$payload->Delay = 1;
		
		$payload2 = new Payload();
		$payload2->Delay = 0;
		
		$this->runSubject([$payload, $payload2], $queueObject);
		
		self::assertEquals(1, $payload->Delay);
		self::assertEquals(2, $payload2->Delay);
	}
	
	public function test_Validate_Required_NoDelayDefaultDelay()
	{
		$queueObject = new QueueObject();
		$queueObjectConfig = new QueueConfig();
		$queueObjectConfig->DelayPolicy = Policy::REQUIRED;
		$queueObjectConfig->MaximalDelay = 10;
		$queueObjectConfig->DefaultDelay = 5;
		$queueObject->Config = $queueObjectConfig;
		
		
		$payload = new Payload();
		$payload->Delay = 1;
		
		$payload2 = new Payload();
		$payload2->Delay = 0;
		
		$this->runSubject([$payload, $payload2], $queueObject);
		
		self::assertEquals(1, $payload->Delay);
		self::assertEquals(5, $payload2->Delay);
	}
	
	
	public function test_Validate_MoreThanMaxDelaySetMax()
	{
		$queueObject = new QueueObject();
		$queueObjectConfig = new QueueConfig();
		$queueObjectConfig->DelayPolicy = Policy::ALLOWED;
		$queueObjectConfig->MaximalDelay = 10;
		$queueObject->Config = $queueObjectConfig;
		
		
		$payload = new Payload();
		$payload->Delay = 15;
		
		$payload2 = new Payload();
		$payload2->Delay = 0;
		
		$this->runSubject([$payload, $payload2], $queueObject);
		
		self::assertEquals(10, $payload->Delay);
		self::assertEquals(0, $payload2->Delay);
	}
	
	public function test_Validate_LessThanMinimalDelaySetMax()
	{
		$queueObject = new QueueObject();
		$queueObjectConfig = new QueueConfig();
		$queueObjectConfig->DelayPolicy = Policy::ALLOWED;
		$queueObjectConfig->MaximalDelay = 10;
		$queueObjectConfig->MinimalDelay = 5;
		$queueObject->Config = $queueObjectConfig;
		
		
		$payload = new Payload();
		$payload->Delay = 0;
		
		$payload2 = new Payload();
		$payload2->Delay = 1;
		
		$this->runSubject([$payload, $payload2], $queueObject);
		
		self::assertEquals(0, $payload->Delay);
		self::assertEquals(5, $payload2->Delay);
	}
}