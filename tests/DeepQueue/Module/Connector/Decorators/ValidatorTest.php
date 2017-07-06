<?php
namespace DeepQueue\Module\Connector\Decorators;


use DeepQueue\DeepQueue;
use DeepQueue\Enums\Policy;
use DeepQueue\Payload;
use DeepQueue\PreparedConfiguration\PreparedQueue;
use PHPUnit\Framework\TestCase;
use Serialization\Json\Serializers\ArraySerializer;
use Serialization\Json\Serializers\PrimitiveSerializer;
use Serialization\Serializers\JsonSerializer;


class ValidatorTest extends TestCase
{
	private function getDeepQueue(): DeepQueue
	{
		$dq = PreparedQueue::InMemory((new JsonSerializer())->add(new ArraySerializer())->add(new PrimitiveSerializer()));
		return $dq;
	}
	
	private function getSubject(): Validator
	{
		$validator = new Validator();
		$validator->setQueueLoader($this->getDeepQueue()->config()->getQueueLoader('validatortest'));
		$validator->setRemoteQueue($this->getDeepQueue()->config()->connector()->getQueue('validatortest'));
		
		return $validator;
	}
	
	
	public function test_Enqueue_ValidPayloads()
	{
		$validator = $this->getSubject();
		
		$payload1 = new Payload();
		$payload2 = new Payload();
		
		$ids = $validator->enqueue([$payload1, $payload2]);
		
		self::assertEquals(2, sizeof($ids));
		
		$validator->dequeueWorkload(2);
	}

	/**
	 * @expectedException \DeepQueue\Exceptions\ValidationException
	 */
	public function test_Enqueue_InvalidPayloads()
	{	
		$validator = $this->getSubject();
		
		$dq = $this->getDeepQueue();
		$queueObject = $dq->getQueueObject('validatortest');
		$queueObject->Config->UniqueKeyPolicy = Policy::REQUIRED;
		$dq->config()->manager()->update($queueObject);
		
		$payload1 = new Payload();
		$payload2 = new Payload();
		
		$ids = $validator->enqueue([$payload1, $payload2]);
		
		self::assertEquals(2, sizeof($ids));
		
		$validator->dequeueWorkload(2);
	}
	
	public function test_Dequeue_CorrectMaxBulkSize()
	{
		$validator = $this->getSubject();
		
		$dq = $this->getDeepQueue();
		$queueObject = $dq->getQueueObject('validatortest');
		$queueObject->Config->MaxBulkSize = 10;
		$queueObject->Config->UniqueKeyPolicy = Policy::ALLOWED;
		$dq->config()->manager()->update($queueObject);
		
		$payload1 = new Payload();
		$payload2 = new Payload();
		
		$validator->enqueue([$payload1, $payload2]);
		
		$workloads = $validator->dequeueWorkload(2);
		
		self::assertEquals(2, sizeof($workloads));
	}
	
	public function test_Dequeue_MaxBulkMoreThanMax()
	{
		$validator = $this->getSubject();
		
		$dq = $this->getDeepQueue();
		$queueObject = $dq->getQueueObject('validatortest');
		$queueObject->Config->MaxBulkSize = 1;
		$queueObject->Config->UniqueKeyPolicy = Policy::ALLOWED;
		$dq->config()->manager()->update($queueObject);
		
		$payload1 = new Payload();
		$payload2 = new Payload();
		
		$validator->enqueue([$payload1, $payload2]);
		
		$workloads = $validator->dequeueWorkload(2);
		
		self::assertEquals(1, sizeof($workloads));
	}
	
	public function test_Dequeue_NegativeCount()
	{
		$validator = $this->getSubject();
		
		self::assertEmpty($validator->dequeueWorkload(-1));
	}
}