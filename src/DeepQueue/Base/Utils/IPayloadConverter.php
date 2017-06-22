<?php
namespace DeepQueue\Base\Utils;


interface IPayloadConverter
{	
	public function prepareAll(array $payloads): array;
	public function getWorkloads(array $payloads): array;
}