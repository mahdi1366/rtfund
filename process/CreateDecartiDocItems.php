<?php
require_once "../header.inc.php";

ini_set("display_errors", "On");
ini_set('max_execution_time', 30000);
ini_set('memory_limit','2000M');

$dt = PdoDataAccess::runquery("select cc.CostID, CostCode, cc.param1,cc.param2,cc.param3
from ACC_CostCodes cc join ACC_blocks b1 on(cc.level1=b1.blockID) 
join ACC_blocks b0 on(b1.GroupID=b0.BlockID)
left join ACC_DocItems di on(di.DocID=9212 AND di.CostID=cc.CostID)

where di.ItemID is null AND b0.blockCode not in(6,7,8) AND not(cc.param1=3 or cc.param2=3 or cc.param3=3)");

$i=0;
echo "Total Codes : " . count($dt) . "<br>";
flush();
ob_flush();
foreach($dt as $row)
{
	echo $row["CostID"] . " - " . $row["CostCode"] . " : ";
	flush();
	ob_flush();
	
	$selectStr = "";
	$fromStr = "";
	switch($row["param1"])
	{
		case "1" : 
			$selectStr .= ",ba1.BankID as param1";
			$fromStr .= " left join ACC_banks ba1 on(cc.param1=1)";
			break;
		case "2":
			$selectStr .= ",b1.BranchID as param1";
			$fromStr .= " left join BSC_branches b1 on(cc.param1=2)";
			break;
		default :
			$selectStr .= ",ci1.ItemID as param1";
			$fromStr .= " left join ACC_CostCodeParamItems ci1 on(cc.param1=ci1.paramID)";
			break;
	}
	switch($row["param2"])
	{
		case "1" : 
			$selectStr .= ",ba2.BankID as param2";
			$fromStr .= " left join ACC_banks ba2 on(cc.param2=1)";
			break;
		case "2":
			$selectStr .= ",b2.BranchID as param2";
			$fromStr .= " left join BSC_branches b2 on(cc.param2=2)";
			break;
		default :
			$selectStr .= ",ci2.ItemID as param2";
			$fromStr .= " left join ACC_CostCodeParamItems ci2 on(cc.param2=ci2.paramID)";
			break;
	}
	switch($row["param3"])
	{
		case "1" : 
			$selectStr .= ",ba3.BankID as param3";
			$fromStr .= " left join ACC_banks ba3 on(cc.param3=1)";
			break;
		case "2":
			$selectStr .= ",b3.BranchID as param3";
			$fromStr .= " left join BSC_branches b3 on(cc.param3=2)";
			break;
		default :
			$selectStr .= ",ci3.ItemID as param3";
			$fromStr .= " left join ACC_CostCodeParamItems ci3 on(cc.param3=ci3.paramID)";
			break;
	}
	$query = "
		insert into ACC_DocItems(DocID, CostID, TafsiliType, 
			TafsiliID, TafsiliType2, TafsiliID2, TafsiliType3, TafsiliID3, 
			param1, param2, param3) 
		
		select  9212, cc.CostID, cc.TafsiliType1, t1.TafsiliID, 
			cc.TafsiliType2, t2.TafsiliID, cc.TafsiliType3, t3.TafsiliID 
           $selectStr
		from ACC_CostCodes cc
		left join ACC_tafsilis t1 on(cc.TafsiliType1=t1.TafsiliType)
		left join ACC_tafsilis t2 on(cc.TafsiliType2=t2.TafsiliType)
		left join ACC_tafsilis t3 on(cc.TafsiliType3=t3.TafsiliType)
		" . $fromStr . 
		" where cc.CostID=?";
	
	PdoDataAccess::runquery($query, $row["CostID"]);
	echo PdoDataAccess::AffectedRows();
	echo "<br>";
	flush();
	ob_flush();
	$i++;
	
}
