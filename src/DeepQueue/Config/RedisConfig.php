<?php
namespace DeepQueue\Config;


use DeepQueue\Base\Config\IRedisConfig;

use Objection\LiteObject;
use Objection\LiteSetup;


/**
 * @property string $Scheme
 * @property string $Host
 * @property string $Port
 * @property array	$SSL
 * @property string $Prefix
 * @property int	$Timeout
 */
class RedisConfig extends LiteObject implements IRedisConfig
{
	protected function _setup()
	{
		return [
			'Scheme'	=> LiteSetup::createString(),
			'Host'		=> LiteSetup::createString(),
			'Port'		=> LiteSetup::createString(),
			'SSL'		=> LiteSetup::createArray(),
			'Prefix'	=> LiteSetup::createString(),
			'Timeout'	=> LiteSetup::createInt()
		];
	}

	
	public function getParameters(): array 
	{
		return [
			'scheme'				=> $this->Scheme,
			'host'					=> $this->Host,
			'port'					=> $this->Port,
			'ssl'					=> $this->SSL,
			'read_write_timeout'	=> $this->Timeout
		];
	}

	public function getOptions(): array 
	{
		return [
			'prefix'	=> "{$this->Prefix}:"
		];
	}
}