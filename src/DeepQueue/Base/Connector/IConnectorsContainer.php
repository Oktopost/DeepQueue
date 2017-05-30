<?php
namespace DeepQueue\Base\Connector;


/**
 * @skeleton
 */
interface IConnectorsContainer extends IConnector
{
	public function setConnector(IConnector $connector);
}