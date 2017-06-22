<?php
namespace DeepQueue\Module\Serialization;


use Serialization\Base\ISerializer;
use Serialization\Json\Serializers\TypedSerializer;


class PayloadSerializer extends TypedSerializer
{
	public function __construct(ISerializer $payloadDataSerializer)
	{
		parent::__construct('PlainPayloadSerializer', new PlainPayloadSerializer($payloadDataSerializer));
	}
}