<?php

	ALTER TABLE `framewor_rtfund`.`acc_costcodes` 
			DROP COLUMN `IsNew`,
			DROP COLUMN `CostGroupID`,
 ADD COLUMN `param1` SMALLINT UNSIGNED NOT NULL DEFAULT 0 ,
 ADD COLUMN `param2` SMALLINT UNSIGNED NOT NULL DEFAULT 0 AFTER `Item1`,
 ADD COLUMN `param3` SMALLINT UNSIGNED NOT NULL DEFAULT 0 AFTER `Item2`;

	 ALTER TABLE `framewor_rtfund`.`acc_costcodes` DROP COLUMN `CostGroupID`,
 CHANGE COLUMN `Item1` `param1` SMALLINT(5) UNSIGNED NOT NULL DEFAULT 0,
 CHANGE COLUMN `Item2` `param2` SMALLINT(5) UNSIGNED NOT NULL DEFAULT 0,
 CHANGE COLUMN `Item3` `param3` SMALLINT(5) UNSIGNED NOT NULL DEFAULT 0;

/*	
insert into ACC_tafsilis(TafsiliCode,TafsiliType,TafsiliDesc,ObjectID) 
		select PersonID,107,concat_ws(' ',fname,lname,CompanyName),PersonID from BSC_persons
		
insert into ACC_tafsilis(TafsiliCode,TafsiliType,TafsiliDesc,ObjectID) 
		select LoanID,104,LoanDesc,LoanID from LON_loans

insert into ACC_tafsilis(TafsiliCode,TafsiliType,TafsiliDesc,ObjectID) 
		select ProcessID,106,ProcessTitle,ProcessID from BSC_processes
*/
