<?php
namespace DeepQueue\Module\Connector\Decorators;


use DeepQueue\Base\IQueueConfig;
use DeepQueue\Manager\QueueConfig;
use DeepQueue\Payload;
use DeepQueue\DeepQueue;
use DeepQueue\Enums\QueueState;
use DeepQueue\Enums\QueueLoaderPolicy;
use DeepQueue\PreparedConfiguration\PreparedQueue;

use PHPUnit\Framework\TestCase;

use Serialization\Serializers\JsonSerializer;
use Serialization\Json\Serializers\ArraySerializer;
use Serialization\Json\Serializers\PrimitiveSerializer;


class QueueStateDecoratorTest extends TestCase
{
	private function getDeepQueue(): DeepQueue
	{
		$dq = PreparedQueue::InMemory((new JsonSerializer())->add(new ArraySerializer())->add(new PrimitiveSerializer()));
		
		$dq->config()
			->setQueueNotExistsPolicy(QueueLoaderPolicy::CREATE_NEW);
		
		return $dq;
	}
	
	private function getConfig(): IQueueConfig
	{
		return new QueueConfig();
	}
	
	private function getSubject(): QueueStateDecorator
	{
		$queueStateDecorator = new QueueStateDecorator();
		$queueStateDecorator->setQueueLoader($this->getDeepQueue()->config()->getQueueLoader('statetest'));
		$queueStateDecorator->setRemoteQueue($this->getDeepQueue()->config()->connector()->getQueue('statetest'));
		
		return $queueStateDecorator;
	}
	
	
	public function test_enqueue_queueRunning()
	{
		$decorator = $this->getSubject();

		$payload1 = new Payload();
		$payload2 = new Payload();
		
		$decorator->enqueue([$payload1, $payload2]);
		
		self::assertEquals(2, sizeof($decorator
			->dequeueWorkload(2, $this->getConfig())));
	}
	
	public function test_enqueue_queueStopped()
	{
		$decorator = $this->getSubject();
		
		$q = $this->getDeepQueue()->getQueueObject('statetest');
		$q->State = QueueState::STOPPED;
		$this->getDeepQueue()->config()->manager()->update($q);

		$payload1 = new Payload();
		$payload2 = new Payload();
		
		$decorator->enqueue([$payload1, $payload2]);
		
		self::assertEquals(0, sizeof($decorator
			->dequeueWorkload(2, $this->getConfig())));
	}
	
	public function test_dequeue_queueRunning()
	{
		$decorator = $this->getSubject();
		
		$dq = $this->getDeepQueue();
		$queueObject = $dq->getQueueObject('statetest');
		$queueObject->State = QueueState::RUNNING;
		$dq->config()->manager()->update($queueObject);
		
		$payload1 = new Payload();
		$payload2 = new Payload();
		
		$decorator->enqueue([$payload1, $payload2]);
		
		$workloads = $decorator->dequeueWorkload(2, $this->getConfig());
		
		self::assertEquals(2, sizeof($workloads));
	}
	
	public function test_dequeue_queueStopped()
	{
		$decorator = $this->getSubject();
		
		$dq = $this->getDeepQueue();
		$queueObject = $dq->getQueueObject('statetest');
		$queueObject->State = QueueState::STOPPED;
		$dq->config()->manager()->update($queueObject);
		
		$payload1 = new Payload();
		$payload2 = new Payload();
		
		$decorator->enqueue([$payload1, $payload2]);
		
		$workloads = $decorator->dequeueWorkload(2, $this->getConfig());
		
		self::assertEquals(0, sizeof($workloads));
	}
	
	public function test_dequeueWithWait_queueStopped()
	{
		$decorator = $this->getSubject();
		
		$dq = $this->getDeepQueue();
		$queueObject = $dq->getQueueObject('statetest');
		$queueObject->State = QueueState::STOPPED;
		$dq->config()->manager()->update($queueObject);
		
		$payload1 = new Payload();
		$payload2 = new Payload();
		
		$decorator->enqueue([$payload1, $payload2]);
		
		$workloads = $decorator->dequeueWorkload(2, $this->getConfig(), 1);
		
		self::assertEquals(0, sizeof($workloads));
	}
}