<?php
namespace DeepQueue\Base\Stream;


use DeepQueue\Base\Stream\IDequeue;
use DeepQueue\Base\Stream\IEnqueue;

interface IQueueStream extends IEnqueue, IDequeue
{

}