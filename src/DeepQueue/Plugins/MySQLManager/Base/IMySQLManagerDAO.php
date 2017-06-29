<?php
namespace DeepQueue\Plugins\MySQLManager\Base;


use DeepQueue\Base\IQueueObject;


interface IMySQLManagerDAO
{
	public function setConfig($config);
	public function upsert(IQueueObject $queue): IQueueObject;
	public function loadById(string $id): ?IQueueObject;
	public function loadByName(string $queueName): ?IQueueObject;
}