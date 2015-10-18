<?php
//-------------------------
// programmer:	Jafarkhani
// create Date: 94.06
//-------------------------
include_once('../header.inc.php');
include_once inc_dataReader;
include_once inc_response;
include_once 'request.class.php';
require_once '../loan/loan.class.php';

$task = $_REQUEST["task"];
switch ($task) {
		
	case "SaveLoanRequest":
		SaveLoanRequest();
		
	case "SelectMyRequests":
		SelectMyRequests();
		
	case "SelectAllRequests":
		SelectAllRequests();
		
	case "SelectAssurances":
		SelectAssurances();
		
	case "DeleteRequest":
		DeleteRequest();
	//-------------------------------------------
	
	case "GetRequestParts":
		GetRequestParts();
		
	case "SavePart":
		SavePart();
		
	case "DeletePart":
		DeletePart();
		
	case "FillParts":
		FillParts();
}

function SaveLoanRequest(){
	
	$obj = new LON_requests();
	PdoDataAccess::FillObjectByArray($obj, $_POST);
	
	$obj->StatusID = $_SESSION["USER"]["IsAgent"] ? 1 : 10;
	if(isset($_POST["sending"]) &&  $_POST["sending"] == "true")
		$obj->StatusID = 10;
	
	if(empty($obj->RequestID))
	{
		$obj->ReqPersonID = $_SESSION["USER"]["PersonID"];		
		$obj->AgentGuarantee = isset($_POST["AgentGuarantee"]) ? "YES" : "NO";
		$result = $obj->AddRequest();
	}
	else
		$result = $obj->EditRequest();
	
	//print_r(ExceptionHandler::PopAllExceptions());
	echo Response::createObjectiveResponse($result, $obj->RequestID);
	die();
}

function SelectMyRequests(){
	
	$dt = LON_requests::SelectAll("r.ReqPersonID=? " . dataReader::makeOrder(), 
			array($_SESSION["USER"]["PersonID"]));
	echo dataReader::getJsonData($dt, count($dt), $_GET["callback"]);
	die();
}

function SelectAllRequests(){
	
	$param = array();
	$where = "1=1 ";
	if(!empty($_REQUEST["RequestID"]))
	{
		$where .= " AND RequestID=:r";
		$param[":r"] = $_REQUEST["RequestID"];
	}
	else
	{
		$branches = FRW_access::GetAccessBranches();
		$where .= " AND BranchID in(" . implode(",", $branches) . ")";
	}
		

	$branches = FRW_access::GetAccessBranches();
	$where = "BranchID in(" . implode(",", $branches) . ")";
	$param = array();
	
	if(!empty($_REQUEST["RequestID"]))
	{
		$where .= " AND RequestID=:r";
		$param[":r"] = $_REQUEST["RequestID"];
	}

	
	$where .= dataReader::makeOrder();
	$dt = LON_requests::SelectAll($where, $param);

	echo dataReader::getJsonData($dt, count($dt), $_GET["callback"]);
	die();
}

function SelectAssurances(){
	
	$temp = PdoDataAccess::runquery("select * from BaseInfo where TypeID=7");
	echo dataReader::getJsonData($temp, count($temp), $_GET["callback"]);
	die();
}

function DeleteRequest(){
	
	$res = LON_requests::DeleteRequest($_POST["RequestID"]);
	echo Response::createObjectiveResponse($res, "");
	die();
}

//------------------------------------------------

function GetRequestParts(){
	
	if(empty($_REQUEST["RequestID"]))
	{
		echo dataReader::getJsonData(array(), 0, $_GET["callback"]);
		die();
	}
	
	$RequestID = $_REQUEST["RequestID"];
	
	$dt = LON_ReqParts::SelectAll("RequestID=?", array($RequestID));
	echo dataReader::getJsonData($dt, count($dt), $_GET["callback"]);
	die();	
}

function SavePart(){
	
	$obj = new LON_ReqParts();
	PdoDataAccess::FillObjectByArray($obj, $_POST);
	
	if($obj->PartID > 0)
		$result = $obj->EditPart();
	else
		$result = $obj->AddPart();

	if(!$result)
	{
		print_r(ExceptionHandler::PopAllExceptions());
		echo Response::createObjectiveResponse(false, "خطا در ثبت مرحله");
		die();
	}
	
	echo Response::createObjectiveResponse(true, "");
	die();
}

function DeletePart(){
	
	$res = LON_ReqParts::DeletePart($_POST["PartID"]);
	echo Response::createObjectiveResponse($res, "");
	die();
}

function FillParts(){
	
}
?>
