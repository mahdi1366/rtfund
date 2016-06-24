<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 95.04
//-----------------------------

require_once '../header.inc.php';
require_once '../docs/doc.class.php';
require_once '../docs/import.data.php';
require_once inc_dataReader;
require_once inc_response;

$task = isset($_REQUEST["task"]) ? $_REQUEST["task"] : "";
if(!empty($task))
	$task();

function selectDeposites() {
	
	$temp = PdoDataAccess::runquery_fetchMode("
		select TafsiliID,CostID,concat_ws('-',b1.BlockDesc,b2.BlockDesc) CostDesc,
			sum(CreditorAmount-DebtorAmount) amount,TafsiliDesc
		from ACC_DocItems 
			join ACC_docs using(DocID)
			join ACC_CostCodes cc using(CostID)
			left join ACC_blocks b1 on(b1.BlockID=cc.level1)
			left join ACC_blocks b2 on(b2.BlockID=cc.level2)
			join ACC_tafsilis using(TafsiliID)
		
		where /*DocStatus != 'RAW'*/ 1=1
			AND CostID in(" . COSTID_ShortDeposite . "," . COSTID_LongDeposite . ")
			AND CycleID=" . $_SESSION["accounting"]["CycleID"] . "
			AND BranchID=" . $_SESSION["accounting"]["BranchID"] . "
		group by TafsiliID,CostID");
	
	$dt = PdoDataAccess::fetchAll($temp, $_GET["start"], $_GET["limit"]);
	//echo PdoDataAccess::GetLatestQueryString();
	echo dataReader::getJsonData($dt, $temp->rowCount(), $_GET ["callback"]);
	die();
}


?>