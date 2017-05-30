<?php
namespace DeepQueue;


use Objection\LiteSetup;
use Objection\LiteObject;


/**
 * @property string|null	$Key
 * @property float|null		$Delay
 * @property mixed			$Payload
 */
class Payload extends LiteObject
{
	/**
	 * @return array
	 */
	protected function _setup()
	{
		return [
			'Key'		=> LiteSetup::createString(null),
			'Delay'		=> LiteSetup::createDouble(null),
			'Payload'	=> LiteSetup::createMixed(null)
		];
	}
	
	
	public function __construct($payload = null)
	{
		parent::__construct();
		
		if (!is_null($payload))
		{
			$this->Payload = $payload;
		}
	}


	public function hasDelay(): bool
	{
		return is_null($this->Delay);
	}
	
	public function hasKey(): bool
	{
		return is_null($this->Key);
	}
	
	public function hasPayload(): bool
	{
		return is_null($this->Payload);
	}
}