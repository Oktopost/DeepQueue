<?php
namespace DeepQueue\Plugins\Logger\Providers\CouchDB\Connector;


use PHPOnCouch\CouchClient;
use PHPOnCouch\CouchDocument;

class CouchDBLogConnector
{	
	/** @var CouchClient */
	private $client;
	
	
	public function __construct(array $config)
	{
		$this->client = new CouchClient($config['dsn'], $config['db']);
		
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