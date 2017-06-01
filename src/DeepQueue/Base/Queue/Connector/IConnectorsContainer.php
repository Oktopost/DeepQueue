<?php
namespace DeepQueue\Base\Queue\Connector;


/**
 * @skeleton
 */
interface IConnectorsContainer extends IConnector
{
	public function setConnector(IConnector $connector);
}