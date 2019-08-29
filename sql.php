<?php

/*
select  
si.ItemID,
b0.blockCode,b0.blockDesc,
b1.blockCode,b1.blockDesc,
b2.blockCode,b2.blockDesc,
b3.blockCode,b3.blockDesc,

bi.InfoDesc TafsiliGroupDesc,t.TafsiliDesc,
bi2.InfoDesc Tafsili2GroupDesc,t2.TafsiliDesc as Tafsili2Desc,
bi3.InfoDesc Tafsili3GroupDesc,t3.TafsiliDesc as Tafsili3Desc,

p1.paramDesc paramDesc1,si.param1,
p2.paramDesc paramDesc2,si.param2,
p3.paramDesc paramDesc3,si.param3

		from ACC_DocItems si
			join ACC_CostCodes cc using(CostID)
            
			join ACC_blocks b1 on(cc.level1=b1.blockID)
            join ACC_blocks b0 on(b1.GroupID=b0.blockID)
			join ACC_blocks b2 on(cc.level2=b2.blockID)
			join ACC_blocks b3 on(cc.level3=b3.blockID)
			
			left join BaseInfo bi on(si.TafsiliType=InfoID AND TypeID=2)
			left join BaseInfo bi2 on(si.TafsiliType2=bi2.InfoID AND bi2.TypeID=2)
			left join BaseInfo bi3 on(si.TafsiliType3=bi3.InfoID AND bi3.TypeID=2)
			
			left join ACC_tafsilis t on(t.TafsiliID=si.TafsiliID)
			left join ACC_tafsilis t2 on(t2.TafsiliID=si.TafsiliID2)
			left join ACC_tafsilis t3 on(t3.TafsiliID=si.TafsiliID3)
			
			left join ACC_CostCodeParams p1 on(p1.ParamID=cc.param1)
			left join ACC_CostCodeParams p2 on(p2.ParamID=cc.param2)
			left join ACC_CostCodeParams p3 on(p3.ParamID=cc.param3)
            
            where DocID=9212

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
