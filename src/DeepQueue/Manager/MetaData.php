<?php
namespace DeepQueue\Manager;


use DeepQueue\Base\Manager\IMetaData;

use Objection\LiteSetup;
use Objection\LiteObject;


/**
 * @property int $Delayed
 * @property int $Enqueued
 */
class MetaData extends LiteObject implements IMetaData
{
	/**
	 * @return array
	 */
	protected function _setup()
	{
		return [
			'Delayed'	=> LiteSetup::createInt(0),
			'Enqueued'	=> LiteSetup::createInt(0)
		];
	}
	
	
	public function hasDelayed(): bool
	{
		return $this->Delayed > 0;
	}

	public function hasEnqueued(): bool
	{
		return $this->Enqueued > 0;
	}
}