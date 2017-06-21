<?php
namespace DeepQueue\Base\Data;


interface IPayloadConverter
{	
	public function serializeAll(array $payloads): array;
	public function deserializeAll(array $payloads): array;
	public function deserializeToWorkloads(array $payloads): array;
	
	public function getDelayed(array $payloads): array;
	public function getImmediately(array $payloads): array;
}