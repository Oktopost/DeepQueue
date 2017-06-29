<?php
use DeepQueue\Base\Queue\IQueue;
use DeepQueue\Payload;
use DeepQueue\DeepQueue;


class PerformanceTester
{
	private const ENQUEUE_QUEUE_NAME = 'performance.test.enqueue';
	private const DEQUEUE_QUEUE_NAME = 'performance.test.dequeue';
	private const FULLCYCLE_QUEUE_NAME = 'performance.test.fullcycle';
	
	
	private $result = [];
	

	private function generatePayloads(int $count)
	{
		$payloads = [];
		
		for($i = 0; $i <= $count - 1; $i++)
		{
			$payloads[] = new Payload($i);
		}

		return $payloads;
	}
	
	private function getTime(float $start, float $stop, $round = 4): float
	{
		return abs(round($stop - $start, $round));
	}
	
	private function createQueues(DeepQueue $deepQueue, int $count)
	{
		$enqueue = $deepQueue->getQueueObject(self::ENQUEUE_QUEUE_NAME);
		$enqueue->Config->MaxBulkSize = $count + 1;
		
		$deepQueue->config()->manager()->update($enqueue);
		
		$dequeue = $deepQueue->getQueueObject(self::DEQUEUE_QUEUE_NAME);
		$dequeue->Config->MaxBulkSize = $count + 1;
		
		$deepQueue->config()->manager()->update($dequeue);
		
		$fullcycle = $deepQueue->getQueueObject(self::FULLCYCLE_QUEUE_NAME);
		$fullcycle->Config->MaxBulkSize = $count + 1;
		
		$deepQueue->config()->manager()->update($fullcycle);
	}
	
	private function testEnqueue(IQueue $queue, array $payloads)
	{
		$startTime = microtime(true);
		
		$queue->enqueueAll($payloads);
		
		$endTime = microtime(true);
		
		unset($queue);
		
		$this->result['enqueue']['time'] = $this->getTime($startTime, $endTime);
	}
	
	private function testDequeue(IQueue $queue, array $payloads)
	{
		$queue->enqueueAll($payloads);
		
		$startTime = microtime(true);
		
		$queue->dequeue(sizeof($payloads));
		
		$endTime = microtime(true);
				
		unset($queue);
		
		$this->result['dequeue']['time'] = $this->getTime($startTime, $endTime);
	}
	
	private function testFullcycle(IQueue $queue, array $payloads)
	{
		$startTime = microtime(true);
		
		$queue->enqueueAll($payloads);
		$queue->dequeue(sizeof($payloads));
		
		$endTime = microtime(true);
		
		$this->result['fullcycle']['time'] = $this->getTime($startTime, $endTime);
	}
	
	
	public function test(DeepQueue $deepQueue, int $count): array 
	{
		$payloads = $this->generatePayloads($count);
		
		$this->createQueues($deepQueue, $count);
		
		$this->testEnqueue($deepQueue->get(self::ENQUEUE_QUEUE_NAME), $payloads);
		$this->testDequeue($deepQueue->get(self::DEQUEUE_QUEUE_NAME), $payloads);
		$this->testFullcycle($deepQueue->get(self::FULLCYCLE_QUEUE_NAME), $payloads);
		
		return $this->result;
	}
}