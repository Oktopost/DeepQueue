<?php
namespace DeepQueue\Base\Plugins\ManagerElements;


use DeepQueue\Base\IQueueObject;


interface IManagerDAO
{
	public function upsert(IQueueObject $queue): void;
	public function load(string $id): ?IQueueObject;
	public function loadByName(string $queueName): ?IQueueObject;
	public function loadAll(): array;
}