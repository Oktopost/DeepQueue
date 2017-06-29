<?php
namespace DeepQueue\Base\Queue\Remote;


use DeepQueue\Payload;


interface IRemoteEnqueue
{
	/**
	 * @param Payload[] $payload
	 * @return string[]|array IDs for each payload
	 */
	public function enqueue(array $payload): array;
}