<?php
namespace DeepQueue\Base\Manager;


/**
 * @property int $Delayed
 * @property int $Enqueued
 */
interface IMetaData
{
	public function hasDelayed(): bool;
	public function hasEnqueued(): bool;
}