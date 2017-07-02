<?php
namespace DeepQueue\Plugins\Logger\Base;


interface ILogProvider
{
	public function write(array $record): void;
	public function level(): int;
}