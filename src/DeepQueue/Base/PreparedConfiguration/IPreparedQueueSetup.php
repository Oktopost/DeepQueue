<?php
namespace DeepQueue\Base\PreparedConfiguration;


use DeepQueue\Base\PreparedConfiguration\Plugins\IPluginConfiguration;
use DeepQueue\DeepQueue;

interface IPreparedQueueSetup
{
	public static function setup(IPluginConfiguration $configuration): DeepQueue;
}