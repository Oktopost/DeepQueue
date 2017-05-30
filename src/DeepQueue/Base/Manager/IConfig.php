<?php
namespace DeepQueue\Base\Manager;


use DeepQueue\Enums\Policy;


/**
 * @property string|Policy	$UniqueKeyPolicy
 * @property string|Policy	$DelayPolicy
 * @property float			$MinimalDelay
 * @property float			$MaximalDelay
 * @property float			$DefaultDelay
 */
interface IConfig
{
	
}