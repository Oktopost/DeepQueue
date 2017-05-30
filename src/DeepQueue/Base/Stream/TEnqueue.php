<?php
namespace DeepQueue\Base\Stream;


use DeepQueue\Payload;


/**
 * @mixin IEnqueue
 */
trait TEnqueue
{
	/**
	 * @param Payload[] $payloads
	 * @return string[]
	 */
	protected abstract function enqueuePayloads(array $payloads): array;
	
	
	/**
	 * @param Payload|mixed $payload
	 */
	public function enqueue($payload, ?string $key = null, ?float $delay = null): string
	{
		if (!($payload instanceof Payload))
		{
			$payload = new Payload($payload);
			$payload->Key = $key;
			$payload->Delay = $delay;
		}
		
		$ids = $this->enqueuePayloads([$payload]);
		
		return $ids[0];
	}

	/**
	 * @param Payload[]|mixed[] $payloads
	 * @return string[]
	 */
	public function enqueueAll(array $payloads, ?float $delay = null): array
	{
		$hasDelay = !is_null($delay);
		
		foreach ($payloads as $key => $payload)
		{
			if (!($payload instanceof Payload))
			{
				$payload = new Payload($payload);
				$payloads[$key] = $payload;
			}
			
			if ($hasDelay)
			{
				$payload->Delay = $delay;
			}
		}
		
		return $this->enqueuePayloads($payloads);
	}
}