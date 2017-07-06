CREATE TABLE IF NOT EXISTS `DeepQueueLog` (
	`Id` 			CHAR(32) NOT NULL,
	`Created` 		DATETIME NULL DEFAULT CURRENT_TIMESTAMP,
	`ParentId`		VARCHAR(244),
	`Level`			VARCHAR(32),
	`Message`		TEXT,
	`Data`			TEXT,

	PRIMARY KEY (`Id`),
	
	INDEX `k_Created`(`Created`),
	INDEX `k_Level` (`Level`),
	INDEX `k_ParentId` (`ParentId`)
) 
ENGINE=InnoDB DEFAULT CHARSET=utf8;