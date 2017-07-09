<?php
namespace DeepQueue\Plugins\Logger\Base;


/**
 * @skeleton
 */
interface ILogger
{
	public function log(int $level, string $message, $data = null, ?string $parentId = null): void;
	public function logException(\Throwable $e, string $message, $data = null): void;
	
	public function error(string $message, $data = null, ?string $parentId = null): void;
	public function warning(string $message, $data = null, ?string $parentId = null): void;
	public function info(string $message, $data = null, ?string $parentId = null): void;
	
	public function addProvider(ILogProvider $provider): void;
}