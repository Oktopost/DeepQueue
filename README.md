# DeepQueue
DeepQueue is a queueing library

## Installation

```shell
composer require oktopost/deep-queue
```
or inside *composer.json*
```json
"require": {
    "oktopost/deep-queue": "^1.0"
}
```

## Usage example

### Basic configuration

```php
$deepQueue = new DeepQueue();

$deepQueue->config()
		->setConnectorPlugin(Connectors::Redis(['prefix' => 'demo.queue']))
		->setManagerPlugin(Managers::MySQL(['user' => 'root', 'db' => 'demo_queue']))
		->setSerializer(DefaultSerializer::get())
		->addLogProvider(new FileLogProvider(__DIR__));

//or using prepared configuration
$deepQueue = PreparedQueue::RedisMySQL(['prefix' => 'demo.queue'], 
    ['user' => 'root', 'db' => 'demo_queue']);

$deepQueue->config()
		->addLogProvider(new FileLogProvider(__DIR__));
```

### Working with queue object configuration
```php
//creating and setting up new queue object
$queueObject = new QueueObject();
$queueObject->Name = 'demo.queue';
$queueObject->Config->DelayPolicy = Policy::ALLOWED;

$deepQueue->config()
	->manager()
	->create($queueObject);

//loading existing queue object
$queueObject = $deepQueue->getQueueObject('demo.queue');
```

### Working with queue connector
```php
//get connector
$queue = $deepQueue->get('demo.queue');

//enqueue data
$payload = new Payload('payload data');

$queue->enqueue($payload);

//or
$payload = new Payload('payload data');
$payload2 = new Payload('payload data2');

$queue->enqueueAll([$payload, $payload2]);


//dequeue data
$payloads = $queue->dequeue(255);
```