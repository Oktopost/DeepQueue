<?php
namespace DeepQueue\Config;


use DeepQueue\Base\Connector\IQueueLoaderBuilder;


class LoaderBuilderFactory
{
	public function get(int $policy): IQueueLoaderBuilder
	{
		throw new \Exception('TODO');
	}
}