<?php
namespace DeepQueue\Utils;


use DeepQueue\Payload;

use PHPUnit\Framework\TestCase;

use Serialization\Serializers\PHPSerializer;


class PayloadConverterTest extends TestCase
{
	private function getSubject(): PayloadConverter
	{
		return new PayloadConverter(new PHPSerializer());
	}

	public function test_prepareAll()
	{
		$payload1 = new Payload([1,2,3]);
		$payload1->Delay = 5;
		
		$payload2 = new Payload(['a', 'b']);
		$payload2->Key = 'test';
		
		$prepared = $this->getSubject()->prepareAll([$payload1, $payload2]);
		
		self::assertEquals(2, sizeof($prepared['keyValue']));
		self::assertEquals(1, sizeof($prepared['now']));
		self::assertEquals(1, sizeof($prepared['delayed']));
		
		self::assertEquals($payload2->Key, $prepared['now'][0]);
		
		self::assertJson($prepared['keyValue'][$payload2->Key]);
	}

	/**
	 * @expectedException \DeepQueue\Exceptions\InvalidUsageException
	 */
	public function test_prepareAll_wrongType()
	{
		$payload1 = new Payload([1,2,3]);
		$payload1->Delay = 5;
		
		$payload2 = ['a', 'b'];
		
		$prepared = $this->getSubject()->prepareAll([$payload1, $payload2]);
		
		self::assertEquals(2, sizeof($prepared['keyValue']));
	}

	public function test_deserializeAll()
	{
		$payload1 = new Payload([1,2,3]);
		$payload1->Delay = 5;
		
		$payload2 = new Payload('a');
		$payload2->Key = 'test';
		
		$prepared = $this->getSubject()->prepareAll([$payload1, $payload2]);
		
		$deserialized = $this->getSubject()->deserializeAll($prepared['keyValue']);
		
		self::assertEquals(2, sizeof($deserialized));
		
		self::assertEquals('a', $deserialized[$payload2->Key]->Payload);
	}

	public function test_getWorkloads()
	{
		$payload1 = new Payload([1,2,3]);
		$payload1->Delay = 5;
		
		$payload2 = new Payload(['a', 'b']);
		$payload2->Key = 'test';
		
		$prepared = $this->getSubject()->prepareAll([$payload1, $payload2]);
		
		$workloads = $this->getSubject()->getWorkloads($prepared['keyValue']);
		
		self::assertEquals(2, sizeof($workloads));
		self::assertEquals($payload2->Key, $workloads[1]->Id);
		
		self::assertEquals('a', $workloads[1]->Payload[0]);
	}
}