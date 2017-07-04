CREATE TABLE `DeepQueueEnqueue` (
	`Id` 			CHAR(32) NOT NULL,
	`QueueName`		VARCHAR(255) NOT NULL,
	
	PRIMARY KEY (`Id`),
	
	INDEX `k_QueueName` (`QueueName`)
) 
ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `DeepQueuePayload` (
	`Id` 			CHAR(32) NOT NULL,
	`QueueName`		VARCHAR(255) NOT NULL,
	`DequeueTime`	DATETIME NOT NULL,
	`Payload`		TEXT,
	
	PRIMARY KEY (`Id`),
	
	INDEX `k_QueueName` (`QueueName`),
	INDEX `k_DequeueTime` (`DequeueTime`)
) 
ENGINE=InnoDB DEFAULT CHARSET=utf8;