<?php
namespace DeepQueue\Base;


use DeepQueue\Enums\Policy;


/**
 * @property string|Policy	$UniqueKeyPolicy
 * @property string|Policy	$DelayPolicy
 * @property float			$MinimalDelay
 * @property float			$MaximalDelay
 * @property float			$DefaultDelay
 * @property int			$MaxBulkSize
 */
interface IConfig
{
	
}