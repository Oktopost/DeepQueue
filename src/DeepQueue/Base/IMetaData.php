<?php
namespace DeepQueue\Base;


/**
 * @property int $Delayed
 * @property int $Enqueued
 */
interface IMetaData
{
	public function hasDelayed(): bool;
	public function hasEnqueued(): bool;
}