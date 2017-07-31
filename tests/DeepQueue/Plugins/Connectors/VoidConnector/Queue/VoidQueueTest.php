<?php
namespace DeepQueue\Plugins\Connectors\VoidConnector\Queue;


use DeepQueue\Payload;
use DeepQueue\Manager\QueueConfig;

use PHPUnit\Framework\TestCase;


class VoidQueueTest extends TestCase
{
	private function getSubject(): VoidQueue
	{
		return new VoidQueue();
	}
	
	
	public function test_enqueue()
	{
		$payload = new Payload('test');
		$payload->Key = 'testk';
		
		self::assertEquals($payload->Key, $this->getSubject()->enqueue([$payload])[0]);
	}
	
	public function test_dequeueWorkload()
	{
		self::assertEmpty($this->getSubject()->dequeueWorkload(255, null, new QueueConfig()));
	}
}