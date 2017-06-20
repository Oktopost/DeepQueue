<?php
namespace DeepQueue;


use Objection\LiteSetup;
use Objection\LiteObject;


/**
 * @property string	$Id
 * @property mixed	$Payload
 */
class Workload extends LiteObject
{
	/**
	 * @return array
	 */
	protected function _setup()
	{
		return [
			'Id'		=> LiteSetup::createString(),
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
}