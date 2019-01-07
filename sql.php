<?php
ALTER TABLE ACC_docs ADD COLUMN `EventID` INTEGER UNSIGNED COMMENT 'کد رویداد' AFTER `imp_AccDocNo`

ALTER TABLE ACC_UserState MODIFY COLUMN `BranchID` SMALLINT(5) UNSIGNED DEFAULT NULL
	
	
/*	
insert into ACC_tafsilis(TafsiliCode,TafsiliType,TafsiliDesc,ObjectID) 
		select PersonID,107,concat_ws(' ',fname,lname,CompanyName),PersonID from BSC_persons
		
insert into ACC_tafsilis(TafsiliCode,TafsiliType,TafsiliDesc,ObjectID) 
		select LoanID,104,LoanDesc,LoanID from LON_loans

insert into ACC_tafsilis(TafsiliCode,TafsiliType,TafsiliDesc,ObjectID) 
		select ProcessID,106,ProcessTitle,ProcessID from COM_processes
*/
