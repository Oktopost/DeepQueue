<?php
namespace DeepQueue\Plugins\Logger\Base;


use DeepQueue\Plugins\Logger\Log\LogEntry;


interface ILogProvider
{
	public function write(LogEntry $record): void;
	public function level(): int;
}