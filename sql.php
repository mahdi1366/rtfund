<?php

ALTER TABLE `framewor_rtfund`.`ACC_DocItems` CHANGE COLUMN `SourceID` `SourceID1` VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
 DROP INDEX `Index_5`,
 ADD INDEX `Index_5` USING BTREE(`SourceID1`);

	 
ALTER TABLE `framewor_rtfund`.`ACC_CostCodes` CHANGE COLUMN `TafsiliType` `TafsiliType1` SMALLINT(5) UNSIGNED DEFAULT NULL;

ALTER TABLE `framewor_rtfund`.`COM_EventRows` MODIFY COLUMN `ComputeItemID` INTEGER UNSIGNED DEFAULT NULL COMMENT 'آیتم محاسبانی';
	
ALTER TABLE `framewor_rtfund`.`ACC_DocItems` ADD COLUMN `param1` VARCHAR(200) NOT NULL DEFAULT '' AFTER `SourceID3`,
 ADD COLUMN `param2` VARCHAR(200) NOT NULL DEFAULT '' AFTER `param1`,
 ADD COLUMN `param3` VARCHAR(200) NOT NULL DEFAULT '' AFTER `param2`;

	 
	 
	 ALTER TABLE `framewor_rtfund`.`ACC_DocItems`
MODIFY COLUMN `SourceID1` VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 0,
 MODIFY COLUMN `SourceID2` INTEGER UNSIGNED NOT NULL DEFAULT 0,
 MODIFY COLUMN `SourceID3` INTEGER UNSIGNED NOT NULL DEFAULT 0,
 ADD COLUMN `SourceID4` INTEGER UNSIGNED NOT NULL DEFAULT 0 AFTER `SourceID3`;
/*	
insert into ACC_tafsilis(TafsiliCode,TafsiliType,TafsiliDesc,ObjectID) 
		select PersonID,107,concat_ws(' ',fname,lname,CompanyName),PersonID from BSC_persons
		
insert into ACC_tafsilis(TafsiliCode,TafsiliType,TafsiliDesc,ObjectID) 
		select LoanID,104,LoanDesc,LoanID from LON_loans

insert into ACC_tafsilis(TafsiliCode,TafsiliType,TafsiliDesc,ObjectID) 
		select ProcessID,106,ProcessTitle,ProcessID from BSC_processes
*/

	 ALTER TABLE `WFM_requests` ADD COLUMN `RequestNo` INTEGER UNSIGNED NOT NULL AFTER `FormID`;

ALTER TABLE `OFC_letters` ADD COLUMN `ProcessID` INTEGER UNSIGNED NOT NULL DEFAULT 0 AFTER `PostalAddress`;

ALTER TABLE `framewor_rtfund`.`BaseTypes` MODIFY COLUMN `SystemID` INTEGER UNSIGNED DEFAULT NULL,
 MODIFY COLUMN `editable` ENUM('YES','NO') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'YES';

ALTER TABLE `framewor_rtfund`.`ACC_CostCodeParamItems` ADD COLUMN `f1` VARCHAR(45) AFTER `ParamValue`,
 ADD COLUMN `f2` VARCHAR(45) AFTER `f1`;
