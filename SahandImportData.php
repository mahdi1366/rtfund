<?php
/*
 * 
 * delete t from tblCoding t left join tblAccDocDetail using(AccCode) where Row is null
 * 
* insert into rtfund.ACC_blocks(LevelID,BlockCode,BlockDesc)
	select 1,AccGroupCode,AccGroupName from tblAccGroup;
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
?>
