<?php
namespace DeepQueue\Plugins\Logger\Providers\CouchDB\Connector;


use PHPOnCouch\CouchClient;
use PHPOnCouch\CouchDocument;


class CouchDBLogConnector
{	
	/** @var CouchClient */
	private $client;
	
	
	public function __construct($dsn, $database)
	{
		$this->client = new CouchClient($dsn, $database);
		
		if (!$this->client->databaseExists())
		{
			$this->client->createDatabase();
		}
	}
	
	
	public function create(array $documentData): void
	{
		$doc = new CouchDocument($this->client);
		$doc->set($documentData);
	}
}