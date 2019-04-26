<?php

	
/*	
insert into ACC_tafsilis(TafsiliCode,TafsiliType,TafsiliDesc,ObjectID) 
		select PersonID,107,concat_ws(' ',fname,lname,CompanyName),PersonID from BSC_persons
		
insert into ACC_tafsilis(TafsiliCode,TafsiliType,TafsiliDesc,ObjectID) 
		select LoanID,104,LoanDesc,LoanID from LON_loans

insert into ACC_tafsilis(TafsiliCode,TafsiliType,TafsiliDesc,ObjectID) 
		select ProcessID,106,ProcessTitle,ProcessID from BSC_processes
*/



ALTER TABLE `framewor_rtfund`.`ACC_ChequeHistory` ADD COLUMN `DocID` INTEGER UNSIGNED DEFAULT 0 AFTER `details`;
