CREATE TABLE IF NOT EXISTS `DeepQueueEnqueue` (
	`Id` 			CHAR(35) NOT NULL,
	`QueueName`		VARCHAR(255) NOT NULL,
	`DequeueTime`	DATETIME NOT NULL,
	
	PRIMARY KEY (`Id`),
	
	INDEX `k_QueueName` (`QueueName`),
	INDEX `k_DequeueTime` (`DequeueTime`)
) 
ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `DeepQueuePayload` (
	`Id` 			CHAR(35) NOT NULL,
	`QueueName`		VARCHAR(255) NOT NULL,
	`Payload`		TEXT,
	
	PRIMARY KEY (`Id`),
	
	INDEX `k_QueueName` (`QueueName`)
) 
ENGINE=InnoDB DEFAULT CHARSET=utf8;