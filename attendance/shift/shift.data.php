<?php
//-------------------------
// programmer:	Jafarkhani
// create Date: 95.01
//-------------------------
include_once('../header.inc.php');
include_once inc_dataReader;
include_once inc_response;
include_once 'Shift.class.php';

$task = $_REQUEST["task"];
switch ($task) {

	default : 
		eval($task. "();");
}

function AddGroup(){
	
	$InfoID = PdoDataAccess::GetLastID("BaseInfo", "InfoID", "TypeID=17");
	
	PdoDataAccess::runquery("insert into BaseInfo(TypeID,InfoID, InfoDesc) 
		values(17,?,?)", array($InfoID+1, $_POST["GroupDesc"]));
	
	echo Response::createObjectiveResponse(true, "");
	die();
}

function SelectShiftGroups(){
	
	$temp = PdoDataAccess::runquery("select * from BaseInfo where TypeID=17");
	echo dataReader::getJsonData($temp, count($temp), $_GET["callback"]);
	die();
}

function DeleteGroup(){
	
	$dt = PdoDataAccess::runquery("select * from ATS_shifts where GroupID=?",array($_POST["GroupID"]));
	if(count($dt)  > 0)
	{
		echo Response::createObjectiveResponse(false, "");
		die();
	}
	
	PdoDataAccess::runquery("delete from BaseInfo where TypeID=17 AND InfoID=?",array($_POST["GroupID"]));
	echo Response::createObjectiveResponse(true, "");
	die();
}

function GetAllShifts() {
	
	$where = "1=1";
	$whereParam = array();
	
	if(isset($_GET["IsCustomer"]))
		$where .= " AND IsCustomer=true";
	
	if(!empty($_GET["GroupID"]))
	{
		$where .= " AND GroupID=:g";
		$whereParam[":g"] = $_GET["GroupID"];
	}
	if(!empty($_REQUEST["ShiftID"]))
	{
		$where .= " AND ShiftID=:l";
		$whereParam[":l"] = $_REQUEST["ShiftID"];
	}
	
	$field = isset($_GET ["fields"]) ? $_GET ["fields"] : "";
	if (isset($_GET ["query"]) && $_GET ["query"] != "") {
		$where .= " AND " . $field . " LIKE :qry ";
		$whereParam[":qry"] = "%" . $_GET["query"] . "%";
	}

	$temp = ATS_shifts::SelectAll($where, $whereParam);
	$no = $temp->rowCount();
	$temp = PdoDataAccess::fetchAll($temp, $_GET["start"], $_GET["limit"]);

	echo dataReader::getJsonData($temp, $no, $_GET["callback"]);
	die();
}

function SaveShift() {

	$obj = new ATS_shifts();
	PdoDataAccess::FillObjectByArray($obj, $_POST);

	$obj->IsCustomer = isset($_POST["IsCustomer"]) ? "YES" : "NO";
	
	if (empty($_POST["ShiftID"]))
		$result = $obj->AddShift();
	else
		$result = $obj->EditShift();

	//print_r(ExceptionHandler::PopAllExceptions());
	echo Response::createObjectiveResponse($result, "");
	die();
}

function DeleteShift() {
	
	$ShiftID = $_POST["ShiftID"];
	$result = ATS_shifts::DeleteShift($ShiftID);
	
	echo Response::createObjectiveResponse($result, "");
	die();
}

?>
