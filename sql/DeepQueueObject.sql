CREATE TABLE IF NOT EXISTS `DeepQueueObject` (
	`Id` 			CHAR(32) NOT NULL,
	`Created` 		DATETIME NULL DEFAULT CURRENT_TIMESTAMP,
	`Modified` 		TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	`Name`			VARCHAR(255) NOT NULL,
	`State`			VARCHAR(32) NOT NULL,
	`Config`		TEXT NOT NULL,
	
	PRIMARY KEY (`Id`),
	
	INDEX `k_Name` (`Name`),
	INDEX `k_Created` (`Created`),
	INDEX `k_Modified` (`Modified`)
) 
ENGINE=InnoDB DEFAULT CHARSET=utf8;