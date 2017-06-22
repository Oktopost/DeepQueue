<?php
namespace DeepQueue;


use DeepQueue\Base\IQueueObject;
use DeepQueue\Enums\Policy;
use DeepQueue\Manager\QueueConfig;
use DeepQueue\Manager\QueueObject;
use DeepQueue\Module\Validator\KeyValidator;
use PHPUnit\Framework\TestCase;


class KeyValidatorTest extends TestCase
{
	private function runSubject(array $payload, IQueueObject $queueObject)
	{
		(new KeyValidator())->validate($payload, $queueObject);
	}
	
	
	public function test_Validate_Ignored()
	{
		$queueObject = new QueueObject();
		$queueObjectConfig = new QueueConfig();
		$queueObjectConfig->UniqueKeyPolicy = Policy::IGNORED;
		$queueObject->Config = $queueObjectConfig;
		
		$payload = new Payload();
		$payload->Key = 5;
		
		$this->runSubject([$payload], $queueObject);
		
		self::assertEquals(null, $payload->Key);
	}
	
	public function test_Validate_Allowed()
	{
		$queueObject = new QueueObject();
		$queueObjectConfig = new QueueConfig();
		$queueObjectConfig->UniqueKeyPolicy = Policy::ALLOWED;
		$queueObject->Config = $queueObjectConfig;
		
		
		$payload = new Payload();
		$payload->Key = 5;
		
		$payload2 = new Payload();
		
		$this->runSubject([$payload, $payload2], $queueObject);
		
		self::assertEquals(5, $payload->Key);
		self::assertEquals(null, $payload2->Key);
	}
	
	public function test_Validate_Forbidden_NoKey()
	{
		$queueObject = new QueueObject();
		$queueObjectConfig = new QueueConfig();
		$queueObjectConfig->UniqueKeyPolicy = Policy::FORBIDDEN;
		$queueObject->Config = $queueObjectConfig;
		
		
		$payload = new Payload();
		
		$payload2 = new Payload();
		
		$this->runSubject([$payload, $payload2], $queueObject);
		
		self::assertEquals(null, $payload->Key);
		self::assertEquals(null, $payload2->Key);
	}

	/**
	 * @expectedException \DeepQueue\Exceptions\ValidationException
	 */
	public function test_Validate_Forbidden_WithKey()
	{
		$queueObject = new QueueObject();
		$queueObjectConfig = new QueueConfig();
		$queueObjectConfig->UniqueKeyPolicy = Policy::FORBIDDEN;
		$queueObject->Config = $queueObjectConfig;
		
		
		$payload = new Payload();
		$payload->Key = 5;
		
		$payload2 = new Payload();
		
		$this->runSubject([$payload, $payload2], $queueObject);
		
		self::assertEquals(5, $payload->Key);
		self::assertEquals(null, $payload2->Key);
	}
	
	public function test_Validate_Required_WithKey()
	{
		$queueObject = new QueueObject();
		$queueObjectConfig = new QueueConfig();
		$queueObjectConfig->UniqueKeyPolicy = Policy::REQUIRED;
		$queueObject->Config = $queueObjectConfig;
		
		
		$payload = new Payload();
		$payload->Key = 1;
		
		$payload2 = new Payload();
		$payload2->Key = 2;
		
		$this->runSubject([$payload, $payload2], $queueObject);
		
		self::assertEquals(1, $payload->Key);
		self::assertEquals(2, $payload2->Key);
	}
	
	/**
	 * @expectedException \DeepQueue\Exceptions\ValidationException
	 */
	public function test_Validate_Required_WithoutKey()
	{
		$queueObject = new QueueObject();
		$queueObjectConfig = new QueueConfig();
		$queueObjectConfig->UniqueKeyPolicy = Policy::REQUIRED;
		$queueObject->Config = $queueObjectConfig;
		
		
		$payload = new Payload();
		$payload->Key = 1;
		
		$payload2 = new Payload();
		
		$this->runSubject([$payload, $payload2], $queueObject);
		
		self::assertEquals(1, $payload->Key);
		self::assertEquals(null, $payload2->Key);
	}
	
}