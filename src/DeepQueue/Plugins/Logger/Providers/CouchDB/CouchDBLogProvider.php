<?php
namespace DeepQueue\Plugins\Logger\Providers\CouchDB;


use DeepQueue\Plugins\Logger\Log\LogEntry;
use DeepQueue\Plugins\Logger\Enum\LogLevel;
use DeepQueue\Plugins\Logger\Base\ILogProvider;
use DeepQueue\Plugins\Logger\Providers\CouchDB\Connector\CouchDBLogConnector;


class CouchDBLogProvider implements ILogProvider
{
	/** @var LogLevel|int */
	private $logLevel;
	
	/** @var CouchDBLogConnector */
	private $connector;

	
	private function prepareData(LogEntry $record): array
	{
		$recordData = $record->toArray();
		unset($recordData['Id']);
		
		$recordData['_id'] = $record->Id;
		$recordData['Data'] = json_encode($record->Data);
		$recordData['Created'] = $record->Created->format('Y-m-d H:i:s');
		
		return $recordData;
	}
	
	
	public function __construct($dsn, $database, $level = LogLevel::ERROR)
	{
		$this->logLevel = $level;

		$this->connector = new CouchDBLogConnector($dsn, $database);
	}
	
	
	public function write(LogEntry $record): void
	{
		$prepared = $this->prepareData($record);
		
		$this->connector->create($prepared);
	}

	public function level(): int
	{
		return $this->logLevel;
	}
}