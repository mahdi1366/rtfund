<?php

	
/*	
insert into ACC_tafsilis(TafsiliCode,TafsiliType,TafsiliDesc,ObjectID) 
		select PersonID,200,concat_ws(' ',fname,lname,CompanyName),PersonID from BSC_persons

update LON_requests left join ACC_tafsilis on(TafsiliType=130 and ObjectID=LoanID)
set LoanID=9
where TafsiliID is null
 * 
insert into ACC_tafsilis(TafsiliCode,TafsiliType,TafsiliDesc,ObjectID) 
select AccountID,200,concat(BankDesc,' - ',AccountDesc),AccountID
from ACC_accounts join ACC_banks using(BankID)

insert into ACC_tafsilis(TafsiliCode,TafsiliType,TafsiliDesc,ObjectID) 
		select ProcessID,150,ProcessTitle,ProcessID from BSC_processes
*/

/**/

ALTER TABLE `framewor_rtfund`.`LON_requests` 
ADD COLUMN `EndingDate` DATE NOT NULL DEFAULT '0000-00-00' AFTER `FundRules`;

ALTER TABLE `framewor_rtfund`.`LON_ReqParts` 
		MODIFY COLUMN `ComputeMode` ENUM('BANK','NEW','NOAVARI') NOT NULL DEFAULT 'BANK';

update LON_ReqParts join LON_requests using(RequestID)
set ComputeMode='NOAVARI'
where ReqPersonID=1003;

ALTER TABLE `framewor_rtfund`.`ACC_ChequeHistory` ADD COLUMN `DocID` INTEGER UNSIGNED DEFAULT 0 AFTER `details`;

ALTER TABLE `framewor_rtfund`.`LON_payments` 
ADD COLUMN `OldFundDelayAmount` DECIMAL(13,0) NOT NULL DEFAULT 0,
ADD COLUMN `OldAgentDelayAmount` DECIMAL(13,0) NOT NULL DEFAULT 0 ;


ALTER TABLE `framewor_rtfund`.`LON_requests` ADD COLUMN `ActDomainID` INTEGER UNSIGNED NOT NULL DEFAULT 0 COMMENT 'حوزه فعالیت' AFTER `FundRules`;

	