<?php
//-------------------------
// programmer:	Jafarkhani
// create Date: 95.01
//-------------------------
include_once('../../header.inc.php');
include_once inc_dataReader;
include_once inc_response;
include_once 'shift.class.php';

$task = $_REQUEST["task"];
switch ($task) {

	default : 
		eval($task. "();");
}

function GetAllShifts() {
	
	$where = "";
	$whereParam = array();
	
	if(!empty($_REQUEST["ShiftID"]))
	{
		$where .= " AND ShiftID=:l";
		$whereParam[":l"] = $_REQUEST["ShiftID"];
	}
	
	$field = isset($_GET ["fields"]) ? $_GET ["fields"] : "title";
	if (isset($_GET ["query"]) && $_GET ["query"] != "") {
		$where .= " AND " . $field . " LIKE :qry ";
		$whereParam[":qry"] = "%" . $_GET["query"] . "%";
	}

	$temp = ATN_shifts::Get($where, $whereParam);
	$no = $temp->rowCount();
	$temp = PdoDataAccess::fetchAll($temp, $_GET["start"], $_GET["limit"]);

	echo dataReader::getJsonData($temp, $no, $_GET["callback"]);
	die();
}

function SaveShift() {

	$obj = new ATN_shifts();
	PdoDataAccess::FillObjectByJsonData($obj, $_POST["record"]);

	if ($obj->ShiftID == "")
		$result = $obj->Add();
	else
		$result = $obj->Edit();
	
	//print_r(ExceptionHandler::PopAllExceptions());
	echo Response::createObjectiveResponse($result, "");
	die();
}

function DeleteShift() {
	
	$obj = new ATN_shifts();
	$obj->ShiftID = $_POST["ShiftID"];
	$result = $obj->Remove();
	
	echo Response::createObjectiveResponse($result, "");
	die();
}

function GetAllPersonShifts(){
	
	$where = "";
	$param = array();
	
	$field = isset($_GET ["fields"]) ? $_GET ["fields"] : "";
	$field = $field == "PersonID" ? "concat_ws(' ',fname,lname,CompanyName)" : $field;
	$field = $field == "ShiftID" ? "s.ShiftTitle" : $field;
	
	if (isset($_GET ["query"]) && $_GET ["query"] != "") {
		$where .= " AND " . $field . " LIKE :qry ";
		$param[":qry"] = "%" . $_GET["query"] . "%";
	}
	
	$temp = ATN_PersonShifts::Get($where, $param, dataReader::makeOrder() . ",FromDate desc");
	$no = $temp->rowCount();
	$temp = PdoDataAccess::fetchAll($temp, $_GET["start"], $_GET["limit"]);

	echo dataReader::getJsonData($temp, $no, $_GET["callback"]);
	die();	
}

function SavePersonShift(){
	
	$obj = new ATN_PersonShifts();
	PdoDataAccess::FillObjectByJsonData($obj, $_POST["record"]);

	if ($obj->RowID == "")
		$result = $obj->Add();
	else
		$result = $obj->Edit();
	
	//print_r(ExceptionHandler::PopAllExceptions());
	echo Response::createObjectiveResponse($result, ExceptionHandler::GetExceptionsToString());
	die();
}

function DeletePersonShift(){
	
	$obj = new ATN_PersonShifts($_POST["RowID"]);
	$result = $obj->Remove();
	
	//print_r(ExceptionHandler::PopAllExceptions());	
	echo Response::createObjectiveResponse($result, ExceptionHandler::GetExceptionsToString());
	die();
}

//..................................

function GetAllHolidays() {
	
	$where = "";
	$whereParam = array();
	
	if(!empty($_REQUEST["Year"]))
	{
		$year = $_REQUEST["Year"];
		$StartDate = DateModules::shamsi_to_miladi($year. "-01-01", "-");
		$EndDate = DateModules::shamsi_to_miladi($year . "-12-" . 
			DateModules::DaysOfMonth($year ,12), "-");
		
		$where .= " AND TheDate between ? AND ?";
		$whereParam[] = $StartDate;
		$whereParam[] = $EndDate;	
	}
	
	$temp = ATN_holidays::Get($where . dataReader::makeOrder(), $whereParam);

	echo dataReader::getJsonData($temp->fetchAll(), $temp->rowCount(), $_GET["callback"]);
	die();
}

function SaveHoliday() {

	$obj = new ATN_holidays();
	PdoDataAccess::FillObjectByJsonData($obj, $_POST["record"]);

	if ($obj->HolidayID == "")
		$result = $obj->Add();
	else
		$result = $obj->Edit();
	
	//print_r(ExceptionHandler::PopAllExceptions());
	echo Response::createObjectiveResponse($result, "");
	die();
}

function DeleteHoliday() {
	
	$obj = new ATN_holidays();
	$obj->HolidayID = $_POST["HolidayID"];
	$result = $obj->Remove();
	
	echo Response::createObjectiveResponse($result, "");
	die();
}


?>
