<?php
namespace DeepQueue\Base\Queue;


use DeepQueue\Payload;


interface IEnqueue
{
	/**
	 * @param Payload|mixed $payload
	 */
	public function enqueue($payload, ?string $key = null, ?float $delay = null): string;

	/**
	 * @param Payload[]|mixed[] $payloads
	 * @return string[]
	 */
	public function enqueueAll(array $payloads, ?float $delay = null): array;
}