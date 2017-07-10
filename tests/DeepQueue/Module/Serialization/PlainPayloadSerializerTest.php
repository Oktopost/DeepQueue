<?php
namespace DeepQueue\Module\Serialization;


use DeepQueue\Payload;
use DeepQueue\Workload;

use PHPUnit\Framework\TestCase;

use Serialization\Json\Serializers\ArraySerializer;
use Serialization\Json\Serializers\PrimitiveSerializer;
use Serialization\Serializers\JsonSerializer;


class PlainPayloadSerializerTest extends TestCase
{
	private function getSubject(): PlainPayloadSerializer
	{
		return new PlainPayloadSerializer((new JsonSerializer())
			->add(new PrimitiveSerializer())
			->add(new ArraySerializer()));
	}
	
	public function test_canSerialize_PassStdClass_ReturnFalse()
	{
		self::assertFalse($this->getSubject()->canSerialize(new \stdClass()));
	}
	
	public function test_canSerializer_PassPayload_ReturnTrue()
	{
		self::assertTrue($this->getSubject()->canSerialize(new Payload()));
	}
	
	public function test_serialize_PassPayload_ReturnArray()
	{
		$payload = new Payload([1,2,3]);
		$payload->Key = 'key';
		
		$meta = Payload::class;
		
		$result = $this->getSubject()->serialize($payload, $meta);
		
		self::assertNotEmpty($result);
		self::assertEquals($payload->Key, $result['Key']);
		self::assertTrue(is_array($result['Payload']));
	}
	
	public function test_deserialize_PassArrayAndPayloadClassName_GetPayloadObject()
	{
		$payload = new Payload([1,2,3]);
		$payload->Key = 'key';
		
		$meta = Payload::class;
		
		$result = $this->getSubject()->serialize($payload, $meta);
		$result['Payload'] = (object)$result['Payload'];
		
		$deserializedPayload = $this->getSubject()->deserialize($result, $meta);
		
		self::assertInstanceOf(Payload::class, $deserializedPayload);
		self::assertEquals($payload->Key, $deserializedPayload->Key);
		self::assertEquals($payload->Payload, $deserializedPayload->Payload);
		
	}

	/**
	 * @expectedException \Serialization\Exceptions\SerializationException
	 */
	public function test_deserializer_PassArrayAndWorkload_GotException()
	{
		$arr = [1,2,3];
		$meta = Workload::class;
		
		$payload = $this->getSubject()->deserialize($arr, $meta);
		
		self::assertInstanceOf(Payload::class, $payload);
	}
}