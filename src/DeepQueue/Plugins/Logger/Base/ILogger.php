<?php
namespace DeepQueue\Plugins\Logger\Base;


/**
 * @skeleton
 */
interface ILogger
{
	public function log(int $level, string $message, $data = null, $parentId = null): void;
	
	public function error(string $message, $data = null, $parentId = null): void;
	public function warning(string $message, $data = null, $parentId = null): void;
	public function info(string $message, $data = null, $parentId = null): void;
	
	public function addProvider(ILogProvider $provider): void;
}