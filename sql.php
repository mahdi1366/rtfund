<?php

/*
insert into ACC_DocItems(DocID, CostID, TafsiliType, TafsiliID, TafsiliType2, TafsiliID2, TafsiliType3, TafsiliID3, 
  DebtorAmount, CreditorAmount, details, locked, SourceType, SourceID1, SourceID2, SourceID3, SourceID4, param1, 
  param2, param3, OldCostCode) 
  select 9212, CostID, TafsiliType, TafsiliID, TafsiliType2, TafsiliID2, TafsiliType3, TafsiliID3, DebtorAmount, 
  CreditorAmount, details, locked, SourceType, SourceID1, SourceID2, SourceID3, SourceID4, param1, param2, param3, 
  OldCostCode from ACC_DocItems where DocID=4292
 
select cc.CostID,CostCode from ACC_CostCodes cc join ACC_blocks b1 on(cc.level1=b1.blockID) 
join ACC_blocks b0 on(b1.GroupID=b0.BlockID)
left join ACC_DocItems di on(di.DocID=9212 AND di.CostID=cc.CostID)
where b0.blockCode not in(6,7,8) AND di.ItemID is null
  
  
 */
	
/*	
insert into ACC_tafsilis(TafsiliCode,TafsiliType,TafsiliDesc,ObjectID) 
		select PersonID,200,concat_ws(' ',fname,lname,CompanyName),PersonID from BSC_persons

insert into ACC_tafsilis(TafsiliCode,TafsiliType,TafsiliDesc,ObjectID) 
select AccountID,200,concat(BankDesc,' - ',AccountDesc),AccountID
from ACC_accounts join ACC_banks using(BankID)

insert into ACC_tafsilis(TafsiliCode,TafsiliType,TafsiliDesc,ObjectID) 
        select b1.ProcessID,150,b1.ProcessTitle,b1.ProcessID 
 *		from BSC_processes b1 left join BSC_processes b2 on(b2.parentID=b1.ProcessID) where b2.ProcessID is null
 * 
update LON_requests left join ACC_tafsilis on(TafsiliType=130 and ObjectID=LoanID)
set LoanID=9
where TafsiliID is null
 * 
 * 
update LON_ReqParts join LON_requests using(RequestID)
set ComputeMode='NOAVARI'
where ReqPersonID in(1003,2051);
*/



ALTER TABLE `framewor_rtfund`.`ACC_ChequeHistory` ADD COLUMN `DocID` INTEGER UNSIGNED DEFAULT 0 AFTER `details`;

ALTER TABLE `framewor_rtfund`.`LON_payments` 
ADD COLUMN `OldFundDelayAmount` DECIMAL(13,0) NOT NULL DEFAULT 0,
ADD COLUMN `OldAgentDelayAmount` DECIMAL(13,0) NOT NULL DEFAULT 0 ;


ALTER TABLE `framewor_rtfund`.`LON_requests` ADD COLUMN `DomainID` INTEGER UNSIGNED NOT NULL DEFAULT 0 COMMENT 'حوزه فعالیت' AFTER `FundRules`;

ALTER TABLE `framewor_rtfund`.`LON_payments` ADD COLUMN `OldFundWage` DECIMAL(13,0) NOT NULL DEFAULT 0 AFTER `OldAgentDelayAmount`,
 ADD COLUMN `OldAgentWage` DECIMAL(13,0) NOT NULL DEFAULT 0 AFTER `OldFundWage`;
