<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 94.06
//-----------------------------

require_once '../header.inc.php';
require_once inc_dataReader;
require_once inc_response;

$task = isset($_POST ["task"]) ? $_POST ["task"] : (isset($_GET ["task"]) ? $_GET ["task"] : "");

switch ($task) {
	
	case "GetAccessBranches":
		GetAccessBranches();
		
	case "SelectCycles":
		SelectCycles();
}

function GetAccessBranches(){
	
	$branches = FRW_access::GetAccessBranches();
	$dt = PdoDataAccess::runquery("select * from BSC_branches where BranchID in(" .
		implode(",", $branches) . ")");
	echo dataReader::getJsonData($dt, count($dt), $_GET["callback"]);
	die();
}

function selectCycles(){
	
	$dt = PdoDataAccess::runquery("select * from ACC_cycles");
	echo dataReader::getJsonData($dt, count($dt), $_GET["callback"]);
	die();
}
?>