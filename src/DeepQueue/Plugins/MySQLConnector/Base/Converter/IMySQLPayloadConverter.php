<?php
namespace DeepQueue\Plugins\MySQLConnector\Base\Converter;


interface IMySQLPayloadConverter
{
	public function prepareAll(string $queueName, array $payloads): array;
	public function getWorkloads(array $payloads): array;
}