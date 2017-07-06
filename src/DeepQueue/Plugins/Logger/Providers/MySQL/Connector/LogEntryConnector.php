<?php
namespace DeepQueue\Plugins\Logger\Providers\MySQL\Connector;


use DeepQueue\Plugins\Logger\Log\LogEntry;

use Objection\Mappers;

use Squid\MySql\Impl\Connectors\Object\Generic\GenericIdConnector;


class LogEntryConnector extends GenericIdConnector
{
	private const TABLE = 'DeepQueueLog';
	
	
	private function initConnector(array $config): void
	{
		$sql = \Squid::MySql();
		$sql->config()->setConfig($config);
		
		$this->setConnector($sql->getConnector());
	}
	
	
	public function __construct(array $config)
	{
		parent::__construct();
		
		$this->initConnector($config);
		
		$mapper = Mappers::simple();
		
		$toJson = function ($o) { return json_encode($o); };
		$fromJson = function ($o) { return json_decode($o); };
		
		$mapper->setDefaultClassName(LogEntry::class)
			->values()
			->callback('Data', $fromJson, $toJson);
		
		$this
			->setTable(self::TABLE)
			->setIdKey('Id')
			->setObjectMap($mapper);
	}
}