<?php
require_once getenv("DOCUMENT_ROOT") . '/framework/configurations.inc.php';

set_include_path(get_include_path() . PATH_SEPARATOR . getenv("DOCUMENT_ROOT") . "/generalClasses");

require_once 'PDODataAccess.class.php';

$date =  mktime(0, 0, 0, 3, 17, 2018);
$date = date("Y-m-d",$date);

for($i=0; $i < 200; $i++)
{
	PdoDataAccess::runquery("insert into jdate values(?,?)", array($date, 
		DateModules::miladi_to_shamsi($date)));
	
	$gdate_array = preg_split('/[\-\/]/',$date);
	$date = mktime(0, 0, 0, $gdate_array[1], $gdate_array[2]+1, $gdate_array[0]);
	$date = date("Y-m-d",$date);
}
/*
SELECT * FROM information_schema.TABLE_CONSTRAINTS
WHERE information_schema.TABLE_CONSTRAINTS.CONSTRAINT_TYPE = 'FOREIGN KEY'
AND information_schema.TABLE_CONSTRAINTS.TABLE_SCHEMA = 'rtfund';
 */

/*
 * SELECT CONCAT('DROP TABLE ', GROUP_CONCAT(table_name), ';') AS query
FROM INFORMATION_SCHEMA.TABLES
WHERE TABLE_ROWS = '0'
AND TABLE_SCHEMA = 's94';


update tblCoding t
left join tblAccDocDetail using(AccCode)
set t.deleted=1
where t.levelCode=3 AND detailID is null ;
 

update tblCoding t
left join tblCoding t3 on(t3.AccParentCode=t.AccCode)
left join tblAccDocDetail d on(d.AccCode=t.AccCode)
set t.deleted=1
where t.levelCode=2 AND t3.AccCode is null AND d.detailID is null;


update tblCoding t
left join tblCoding t2 on(t2.AccParentCode=t.AccCode)
left join tblAccDocDetail d on(d.AccCode=t.AccCode)
set t.deleted=1
where t.levelCode=1 AND t2.AccCode is null AND d.detailID is null;

--------------------------------------------------------------------------------

insert into rtfund.ACC_blocks(levelID,BlockCode,BlockDesc,GroupID,parent,AccCode)
SELECT 1,AccCode,AccName,AccGroupCode,0,AccCode FROM tblCoding
where levelCode=1 AND deleted=0;

insert into rtfund.ACC_CostCodes(level1,CostCode)
SELECT b1.blockID,concat(b1.BlockCode)
FROM rtfund.ACC_blocks b1
where b1.levelID=1;

********************

insert into rtfund.ACC_blocks(levelID,BlockCode,BlockDesc,parent,AccCode)
SELECT 2,lpad(substring(AccCode,5),2,0),AccN,AccParentCode,AccCode FROM tblCoding
where levelCode=2 AND deleted=0 AND AccParentCode<>760

insert into rtfund.ACC_CostCodes(level1,level2,CostCode)
	SELECT b2.blockID,b1.blockID,concat(b2.BlockCode,'-',b1.BlockCode)
  FROM rtfund.ACC_blocks b1
	join rtfund.ACC_blocks b2 on(b1.parent=b2.AccCode AND b2.levelID=1)
	where b1.levelID=2;

********************

insert into rtfund.ACC_blocks(levelID,BlockCode,BlockDesc,parent,AccCode)
SELECT 3,lpad(substring(AccCode,5),2,0),AccN,AccParentCode,AccCode FROM tblCoding
where levelCode=3 AND deleted=0 AND AccParentCode in('500-01')

insert into rtfund.ACC_CostCodes(level1,level2,level3,CostCode)
	SELECT b3.blockID,b2.blockID,b1.blockID,concat(b3.BlockCode,'-',b2.BlockCode,'-',b1.BlockCode)
  FROM rtfund.ACC_blocks b1
	join rtfund.ACC_blocks b2 on(b1.parent=b2.AccCode AND b2.levelID=2)
	join rtfund.ACC_blocks b3 on(b2.parent=b3.AccCode AND b3.levelID=1)
	where b1.levelID=3;

--------------------------------------------------------------------------------





 * 
 * update rtfund.ACC_blocks set AccCode=BlockCode where levelID=1;
 * 
 * 
 * insert into rtfund.ACC_blocks(levelID,BlockCode,BlockDesc,parent,AccCode)
	SELECT 2,AccCode,AccName,AccGroupCode,AccCode FROM sahand.tblCoding	where levelCode=1
 * 
 * 
 * select * from sahand.tblCoding where levelCode=2 // check for wrong levelcodes
 * select * sahand.tblAccDocDetail where AccCode in('500-500-27','500-500-28','500-500-29')
 * update sahand.tblAccDocDetail
set AccCode=substring(AccCOde,5)
where AccCode in('500-500-27','500-500-28','500-500-29')
 * 
 * insert into rtfund.ACC_blocks(levelID,BlockCode,BlockDesc,parent,AccCode)
SELECT 3,lpad(substring(AccCode,5),2,0),AccN,AccParentCode,AccCode FROM sahand.tblCoding
where levelCode=2
 * 
 * 
 * insert into rtfund.ACC_CostCodes(level1,level2,level3,CostCode)
	SELECT b3.blockID,b2.blockID,b1.blockID,concat(b3.BlockCode,'-',b2.BlockCode,'-',b1.BlockCode) FROM rtfund.ACC_blocks b1
	join rtfund.ACC_blocks b2 on(b1.parent=b2.AccCode AND b2.levelID=2)
	join rtfund.ACC_blocks b3 on(b2.parent=b3.AccCode AND b3.levelID=1)
	where b1.levelID=3
 * 
 */

/*

select * from tblCoding t1
left join tblCoding t2 on(t1.AccCode=t2.AccParentCode)
where t1.levelCode=1 AND t2.AccCode is null;


select t1.AccCode,t2.AccCode,t1.AccName,t2.AccName from tblCoding t2
join tblCoding t1 on(t2.AccParentCode=t1.AccCode)
left join tblCoding t3 on(t2.AccCode=t3.AccParentCode)
where t2.levelCode=2 AND t3.AccCode is null;


SELECT t3.AccCode,t2.AccCode,t1.AccCode, t3.AccName,t2.AccName,t1.AccName
FROM tblCoding t1
join tblCOding t2 on(t1.AccParentCode=t2.AccCode)
join tblCOding t3 on(t2.AccParentCode=t3.AccCode)
where t1.levelCode=3;
 * 
 * 
 */



/*
********************************************************************************
insert into rtfund.BSC_persons(IsReal,fname,lname,CompanyName,PhoneNo,mobile,address,imp_CustomerCode)
SELECT if(locate('شرکت',CustName)=0,'YES','NO'),
       if(locate('شرکت',CustName)=0,substring(CustName,1,locate(' ',CustName) ),null),
       if(locate('شرکت',CustName)=0,substring(CustName,locate(' ',CustName)+1 ),null),
       if(locate('شرکت',CustName)=0,null,CustName),
       Telephone,
       Mobile,
       Adress,
       CustomerCode
FROM tblWareCustomers where CustTypeCode<>13 ;

update BSC_persons set
fname = replace(replace(fname,'ي','ی'),'ك','ک'),
lname = replace(replace(lname,'ي','ی'),'ك','ک'),
CompanyName = replace(replace(CompanyName,'ي','ی'),'ك','ک'),
address = replace(replace(address,'ي','ی'),'ك','ک')

ALTER TABLE `rtfund`.`ACC_tafsilis` AUTO_INCREMENT = 1000;

insert into rtfund.ACC_tafsilis(TafsiliCode,TafsiliType,TafsiliDesc,ObjectID)
select PersonID,1,if(IsReal='YES', concat(fname,' ',lname), CompanyName),PersonID
from rtfund.BSC_persons;
 * 
update rtfund.ACC_tafsilis set TafsiliDesc= replace(TafsiliDesc,'  ',' ');
update rtfund.ACC_tafsilis  set TafsiliDesc= trim(TafsiliDesc);
********************************************************************************
ALTER TABLE `rtfund`.`LON_requests` MODIFY COLUMN `ReqDate` VARCHAR(100) NOT NULL COMMENT 'تاریخ درخواست';

insert into rtfund.LON_requests(BranchID,LoanID,ReqPersonID,ReqDate,ReqAmount,StatusID,ReqDetails,
                                LoanPersonID,guarantees,SupportPersonID,imp_VamCode)
SELECT 1,exp_LoanID,ifnull(exp_AgentID,PersonID),
    concat(13,replace(PayDate,'/','-')),PayValue,70,comment,
		PersonID,exp_InfoID,
		if(VamTypeCode=27,exp_AgentID,null),VamCode
FROM tblVam
join tblVamType using(VamTypeCode)
join rtfund.BSC_persons on(GirandehCode=imp_CustomerCode)
left join tblZemanatTypeTaavon using(ZemanatTypeCode)
 * 
 */
/*$temp = PdoDataAccess::runquery("select * from LON_requests");
foreach($temp as $row)
	PdoDataAccess::runquery("update LON_requests set ReqDate='" . 
			DateModules::shamsi_to_miladi($row["ReqDate"],"-") . "' where RequestID=" . $row["RequestID"]);
*/
/*
ALTER TABLE `rtfund`.`LON_requests` MODIFY COLUMN `ReqDate` DATETIME NOT NULL COMMENT 'تاریخ درخواست';
 
update LON_requests set reqDetails = replace(replace(reqDetails,'ي','ی'),'ك','ک')
 * 
********************************************************************************
insert into rtfund.LON_ReqParts(RequestID,PartDesc,PartDate,PartAmount,installmentCount,IntervalType,
  PayInterval,DelayMonths,CustomerWage,FundWage)

SELECT RequestID,'مرحله اول',ReqDate,ReqAmount,NoAghsat,'MONTH',1,ifnull(DelayMonthCount,0),
BenefitPercent,
BenefitPercent

FROM tblVam
join rtfund.LON_requests on(VamCode=imp_VamCode)
join tblVamType using(VamTypeCode)
join rtfund.BSC_persons on(GirandehCode=imp_CustomerCode)
left join tblZemanatTypeTaavon using(ZemanatTypeCode)
 * 
 update LON_ReqParts set PartID=RequestID
********************************************************************************
ALTER TABLE `rtfund`.`LON_installments` MODIFY COLUMN `InstallmentDate` VARCHAR(20) NOT NULL COMMENT 'تاریخ سررسید',
 MODIFY COLUMN `PaidDate` VARCHAR(20) DEFAULT NULL COMMENT 'تاریخ پرداخت';

insert into rtfund.LON_installments(PartID,InstallmentDate,InstallmentAmount,PaidType,
    PaidDate,PaidAmount,
    PaidBillNo,IsPaid,ChequeNo,details,imp_SerialNo)

SELECT RequestID,concat('13',GhestDate),GhestValue,if(payValue>0,if(PaymentTypeCode=0,1,PaymentTypeCode),0),
  if(payDate='__',null,concat('13',PayDate)),
  PayValue+delayValue,if(PaymentTypeCode<>9,FishNo,0),if(PayValue>0,'YES','NO'),
  if(PaymentTypeCode=9,FishNo,0),comment,serialNo
FROM tblVamDetail
join tblVam using(VamCode)
join rtfund.LON_requests on(VamCode=imp_VamCode)

 */
/*
$temp = PdoDataAccess::runquery("select * from LON_installments where length(InstallmentDate)=10");
foreach($temp as $row)
	PdoDataAccess::runquery("update LON_installments set ".
			" InstallmentDate=" . ($row["InstallmentDate"] == "" ? "null" : "'" .
				DateModules::shamsi_to_miladi($row["InstallmentDate"],"-") . "'") . ",
			PaidDate= " . ($row["PaidDate"] == "" ? "null" : "'" .
				DateModules::shamsi_to_miladi($row["PaidDate"],"-") . "'") . "
			where InstallmentID=" . $row["InstallmentID"]);
*/
/*
update rtfund.LON_ReqParts join 
	(select PartID,count(*) cnt from rtfund.LON_installments group by PartID)t using(PartID)
set InstallmentCount=cnt where InstallmentCount=0;
 
ALTER TABLE `rtfund`.`LON_installments` MODIFY COLUMN `InstallmentDate` DATE NOT NULL COMMENT 'تاریخ سررسید',
 MODIFY COLUMN `PaidDate` DATETIME DEFAULT NULL COMMENT 'تاریخ پرداخت';

update rtfund.LON_installments set ChequeNo=null where ChequeNo=0;

update rtfund.LON_installments set details = replace(replace(details,'ي','ی'),'ك','ک')
 * 
 * 
delete l2 from LON_installments l1
join LON_installments l2 on(l1.InstallmentID<l2.InstallmentID
AND l1.IsPaid=l2.IsPaid AND l1.PartID=l2.PartID AND
l1.InstallmentDate=l2.InstallmentDate AND
l1.InstallmentAmount = l2.InstallmentAmount)
where l1.IsPaid='NO' ;

insert into LON_BackPays(PartID,PayType,PayAmount,PayDate,PayBillNo,ChequeNo,ChequeBank,ChequeBranch,details)
select PartID,PaidType,PaidAmount,PaidDate,PaidBillNo,ChequeNo,ChequeBank,ChequeBranch,details
from LON_installments where IsPaid='YES'
 * 
********************************************************************************
	insert into rtfund.DMS_Documents(DocDesc,DocType,ObjectType,ObjectID,IsConfirm,IsReturned,imp_DetailID)

SELECT if(BankName like '%دفتر%' or BankName like '%صندوق%' or BankName like '%وث%' or
        BankName like '%ملک%' , concat(BankName,' - ',CheckNumber), BankName),
case when BankName like '%سفته%' then 4
  when BankName like '%دفتر%' or BankName like '%صندوق%' then 7
  when BankName like '%وث%' or BankName like '%ملک%' then 1 else 3 end ,
  'loan', RequestID,'YES',if(StatusCode=1,'NO','YES'),
  DetailID
FROM Tbl_ZemanatCheckDetail join Tbl_ZemanatCheck using(DocNo)
join rtfund.LON_requests on(VamCode=imp_VamCode)
where VamCode>0;

update DMS_documents set DocDesc = replace(replace(DocDesc,'ي','ی'),'ك','ک')

insert into rtfund.DMS_DocParamValues(DocumentID,ParamID,ParamValue)
select DocumentID,ParamID,
  case ParamID when 1 then CheckNumber
               when 2 then CheckValue
               when 3 then BankName
               when 5 then CheckNumber
               when 6 then CheckValue
               when 7 then CheckValue
               when 9 then CheckValue
end
from rtfund.DMS_documents join Tbl_ZemanatCheckDetail z on(imp_DetailID=DetailID)
join rtfund.DMS_DocParams using(DocType)
where ParamID<>4;
 
update rtfund.DMS_DocParamValues set ParamValue = replace(replace(ParamValue,'ي','ی'),'ك','ک')
********************************************************************************

insert into imp_mergePersons 
select p1.PersonID,p2.PersonID
from BSC_persons p1
join BSC_persons p2 on(p1.PersonID<>p2.PersonID)
where concat_ws('',p1.fname,p1.lname,p1.CompanyName)= concat_ws('',p2.fname,p2.lname,p2.CompanyName);

delete t from ACC_tafsilis t left join BSC_persons on(ObjectID=PersonID)
where ObjectID>0 AND TafsiliType=1 AND PersonID is null





















********************************************************************************
insert into rtfund.ACC_DocItems(DocID,CostID,TafsiliType,TafsiliID,DebtorAmount,CreditorAmount,details,locked)

SELECT 1,65,1,TafsiliID, if(sum(BesValue-BedValue)<0,-1*sum(BesValue-BedValue),0),
  if(sum(BesValue-BedValue)>0,sum(BesValue-BedValue),0),'انتقال از نرم افزار قديم','YES'
FROM s94.tblAccDocDetail
join tblAcoAccount on(AcoNum=substr(AccCode,8))
join rtfund.BSC_persons on(AccountantCode=imp_CustomerCode)
join rtfund.ACC_tafsilis on(TafsiliType=1 AND ObjectID=PersonID)
where AccCode like '209-10-%'
group by AccCode;

update ACC_DocItems set details = replace(replace(details,'ي','ی'),'ك','ک')

ایجاد ردیف صندوق از مجموع کل ردیف های اضافه شده در سند 1
********************************************************************************
اضافه به سند سپرده های کوتاه مدت بر اساس کوئری زیر

SELECT AccCode, sum(BesValue-BedValue) FROM tblAccDocDetail  where AccCode like '210%'
group by AccCode; 

ایجاد ردیف صندوق از مجموع کل ردیف های اضافه شده به سند 2
********************************************************************************
insert into rtfund.ACC_DocItems(DocID,CostID,DebtorAmount,locked)
SELECT 3,CostID, sum(BedValue-BesValue), 'YES'
FROM tblAccDocDetail join tblCoding using(AccCode)
join rtfund.imp_MapCodes using(AccCode)
where AccCode like '500%'
group by AccCode;
 
update ACC_DocItems set details = replace(replace(details,'ي','ی'),'ك','ک')

ایجاد ردیف صندوق از مجموع هزینه ها 
********************************************************************************
ALTER TABLE `rtfund`.`ACC_docs` MODIFY COLUMN `DocDate` VARCHAR(50) NOT NULL COMMENT 'تاریخ سند';

insert into rtfund.ACC_docs(CycleID,BranchID,LocalNo,DocDate,RegDate,
DocStatus,DocType,description,imp_AccDocNo)
SELECT 1394,1,TempDocNo,DocDate,now(),'CONFIRM',2,comment,ACCDocNo FROM s94.tblAccDoc order by TempDocNo;
*/
/*$temp = PdoDataAccess::runquery("select * from ACC_docs where length(DocDate)=8");
foreach($temp as $row)
	PdoDataAccess::runquery("update ACC_docs set ".
			" DocDate=" . ($row["DocDate"] == "" ? "null" : "'" .
				DateModules::shamsi_to_miladi("13". $row["DocDate"],"-") . "'") . "
			where DocID=" . $row["DocID"]);
*/
/*
update ACC_Docs set description = replace(replace(description,'ي','ی'),'ك','ک');
ALTER TABLE `rtfund`.`ACC_docs` MODIFY COLUMN `DocDate` DATE NOT NULL COMMENT 'تاریخ سند';

********************************************************************************
 */
/* 
update tblCoding set AccName = replace(replace(AccName,'ي','ی'),'ك','ک');
update tblCoding set AccName= replace(AccName,'  ',' ');
 * 
 * 
insert into rtfund.imp_MapCodes
select
ifnull(CostID, case substr(AccCode,1,6)
  when '110-16' then 125
  when '110-19' then 125
  when '110-23' then 50
  when '110-27' then 125 end),
AccCode, TafsiliID ,
case substr(AccCode,1,6)
  when '110-16' then 1002
  when '110-19' then 1004
  when '110-27' then 1003
  else null end
from tblAccDocDetail join tblCoding using(AccCode)
left join rtfund.ACC_tafsilis on(AccName like concat('%',TafsiliDesc, '%') )
left join rtfund.ACC_CostCodes c on(c.CostCode= substr(AccCode,1,6))
where AccCode like '110%' AND length(AccCode) > 6
group by AccCode;
 * 
 * 
insert into rtfund.imp_MapCodes
select CostID,AccCode, TafsiliID , null
from tblAccDocDetail join tblCoding using(AccCode)
left join rtfund.ACC_tafsilis on(AccName like concat('%',TafsiliDesc, '%') )
left join rtfund.ACC_CostCodes c on(c.CostCode= substr(AccCode,1,6))
where AccCode like '209%' AND length(AccCode) > 6
group by AccCode;
 * 
 * 
insert into rtfund.ACC_DocItems(DocID,CostID,TafsiliType,TafsiliID,Tafsili2Type,Tafsili2ID,
  DebtorAmount,CreditorAmount,details)
select DocID,CostID,tafsiliType,
  TafsiliID,tafsiliType2,TafsiliID2,BedValue,BesValue,comment
from tblAccDocDetail join rtfund.ACC_docs on(imp_AccDocNo=AccDocNo)
join rtfund.imp_MapCodes using(AccCode)
 * 
 *  */

/*
SELECT AccCode,AccName, count(*) FROM sahand.tblAccDocDetail
join tblCoding using(AccCode)
left join rtfund.imp_MapCodes using(AccCode)
where CostID is null
group by AccCode;

insert into rtfund.imp_MapCodes
select
CostID,
AccCode, TafsiliID , null
from tblAccDocDetail join tblCoding using(AccCode)
left join rtfund.ACC_tafsilis on(AccName like concat('%',TafsiliDesc, '%') )
left join rtfund.ACC_CostCodes on(CostCode= substr(AccCode,1,6))
where AccCode like '209%' AND length(AccCode) > 6
group by AccCode;

*/






/*
tblWareCustomers
tblVam
tblVamDetail
 */
?>
