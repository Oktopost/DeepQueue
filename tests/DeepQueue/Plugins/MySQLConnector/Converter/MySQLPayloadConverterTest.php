<?php
namespace DeepQueue\Plugins\MySQLConnector\Converter;


use DeepQueue\Payload;
use PHPUnit\Framework\TestCase;

use Serialization\Serializers\PHPSerializer;


class MySQLPayloadConverterTest extends TestCase
{
	private const TEST_QUEUE_NAME = 'test.queue';
	
	private function getSubject(): MySQLPayloadConverter
	{
		return new MySQLPayloadConverter(new PHPSerializer());
	}

	public function test_prepareAll()
	{
		$payload1 = new Payload([1,2,3]);
		$payload1->Delay = 5;
		
		$payload2 = new Payload(['a', 'b']);
		$payload2->Key = 'test';
		
		$prepared = $this->getSubject()->prepareAll(self::TEST_QUEUE_NAME, [$payload1, $payload2]);
		
		self::assertEquals(2, sizeof($prepared['payloads']));
		self::assertEquals(2, sizeof($prepared['enqueue']));
		
		self::assertEquals($payload2->Key, $prepared['enqueue'][1]['Id']);
		
		self::assertEquals(self::TEST_QUEUE_NAME, $prepared['enqueue'][1]['QueueName']);
		self::assertEquals(self::TEST_QUEUE_NAME, $prepared['payloads'][0]['QueueName']);
		
		self::assertJson($prepared['payloads'][0]['Payload']);
	}

	/**
	 * @expectedException \DeepQueue\Exceptions\InvalidUsageException
	 */
	public function test_prepareAll_wrongType()
	{
		$payload1 = new Payload([1,2,3]);
		$payload1->Delay = 5;
		
		$payload2 = ['a', 'b'];
		
		$prepared = $this->getSubject()->prepareAll(self::TEST_QUEUE_NAME, [$payload1, $payload2]);
		
		self::assertEquals(2, sizeof($prepared['payloads']));
	}

	public function test_getWorkloads()
	{
		$payload1 = new Payload([1,2,3]);
		$payload1->Delay = 5;
		
		$payload2 = new Payload(['a', 'b']);
		$payload2->Key = 'test';
		
		$prepared = $this->getSubject()->prepareAll(self::TEST_QUEUE_NAME, [$payload1, $payload2]);
		
		$workloads = $this->getSubject()->getWorkloads($prepared['payloads']);
		
		self::assertEquals(2, sizeof($workloads));
		self::assertEquals($payload2->Key, $workloads[1]->Id);
		
		self::assertEquals('a', $workloads[1]->Payload[0]);
	}
}