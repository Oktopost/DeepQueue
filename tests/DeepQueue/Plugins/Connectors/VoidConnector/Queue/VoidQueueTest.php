<?php
namespace DeepQueue\Plugins\Connectors\VoidConnector\Queue;


use DeepQueue\Payload;

use PHPUnit\Framework\TestCase;


class VoidQueueTest extends TestCase
{
	private function getSubject(): VoidQueue
	{
		return new VoidQueue();
	}
	
	
	public function test_enqueue()
	{
		self::assertEmpty($this->getSubject()->enqueue([new Payload('test')]));
	}
	
	public function test_dequeueWorkload()
	{
		self::assertEmpty($this->getSubject()->dequeueWorkload(255));
	}
}