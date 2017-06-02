ALTER TABLE `krrtfir_rtfund`.`FRW_access` ADD COLUMN `GroupID` INTEGER UNSIGNED NOT NULL DEFAULT 0
AFTER `PersonID`;
ALTER TABLE `krrtfir_rtfund`.`FRW_access` MODIFY COLUMN `PersonID` INTEGER NOT NULL DEFAULT 0;
ALTER TABLE `krrtfir_rtfund`.`FRW_access` MODIFY COLUMN `GroupID` INTEGER NOT NULL DEFAULT 0,
 DROP PRIMARY KEY,
 ADD PRIMARY KEY  USING BTREE(`MenuID`, `PersonID`, `GroupID`);

 
 select * from BaseInfo where TypeID=75
 
 
 ALTER TABLE `krrtfir_rtfund`.`ACC_blocks` ADD COLUMN `EndDate` DATE NOT NULL AFTER `GroupID`;
