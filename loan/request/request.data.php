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
		
	case "ChangeRequesrStatus":
		ChangeRequesrStatus();
	//-------------------------------------------
	
	case "GetRequestParts":
		GetRequestParts();
		
	case "SavePart":
		SavePart();
		
	case "DeletePart":
		DeletePart();
		
	case "FillParts":
		FillParts();
		
	//----------------------------------------------
		
	case "GetPartPayments":
		GetPartPayments();
		
	case "ComputePartPayments":
		ComputePartPayments();
}

function SaveLoanRequest(){
	
	$obj = new LON_requests();
	PdoDataAccess::FillObjectByArray($obj, $_POST);
	
	if(isset($_POST["sending"]) &&  $_POST["sending"] == "true")
		$obj->StatusID = 10;
	
	$obj->AgentGuarantee = isset($_POST["AgentGuarantee"]) ? "YES" : "NO";	
	
	if(empty($obj->RequestID))
	{
		$obj->StatusID = $_SESSION["USER"]["IsAgent"] ? 1 : 10;
		$obj->ReqPersonID = $_SESSION["USER"]["PersonID"];		
		$obj->AgentGuarantee = isset($_POST["AgentGuarantee"]) ? "YES" : "NO";
		$result = $obj->AddRequest();
		if($result)
			ChangeStatus($obj->RequestID,$obj->StatusID, "", true);
	}
	else
	{
		$result = $obj->EditRequest();
		if($result)
			ChangeStatus($obj->RequestID,$obj->StatusID, "", true);
	}
	
	//print_r(ExceptionHandler::PopAllExceptions());
	echo Response::createObjectiveResponse($result, $obj->RequestID);
	die();
}

function SelectMyRequests(){
	
	if($_SESSION["USER"]["IsAgent"] == "YES")
		$where = "r.ReqPersonID=" . $_SESSION["USER"]["PersonID"];
	if($_SESSION["USER"]["IsCustomer"] == "YES")
		$where = "r.LoanPersonID=" . $_SESSION["USER"]["PersonID"];
	
	$dt = LON_requests::SelectAll($where . dataReader::makeOrder());
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
	$param = array();
	
	if(!empty($_REQUEST["RequestID"]))
	{
		$where .= " AND RequestID=:r";
		$param[":r"] = $_REQUEST["RequestID"];
	}

	
	$where .= dataReader::makeOrder();
	$dt = LON_requests::SelectAll($where, $param);
	print_r(ExceptionHandler::PopAllExceptions());
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

function ChangeStatus($RequestID, $StatusID, $description = "", $LogOnly = false){
	
	if(!$LogOnly)
	{
		$obj = new LON_requests();
		$obj->RequestID = $_POST["RequestID"];
		$obj->StatusID = $StatusID;
		if(!$obj->EditRequest())
			return false;
	}
	$result = PdoDataAccess::runquery("insert into LON_ReqFlow(RequestID,PersonID,StatusID,ActDate,description) 
		values(?,?,?,?,?)", array(
			$RequestID,
			$_SESSION["USER"]["PersonID"],
			$StatusID,
			PDONOW,
			$description
		));
	
	return $result;
}

function ChangeRequesrStatus(){
	
	$result = ChangeStatus($_POST["RequestID"],$_POST["StatusID"],$_POST["desc"]);
	Response::createObjectiveResponse($result, "");
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

//------------------------------------------------

function GetPartPayments(){
	
	$dt = LON_PartPayments::SelectAll("PartID=?", array($_REQUEST["PartID"]));
	echo dataReader::getJsonData($dt, count($dt), $_GET["callback"]);
	die();
}

//....................
function PMT($CustomerFee, $PayCount, $PartAmount, $YearMonths) {  
	$CustomerFee = $CustomerFee/($YearMonths*100);
	$PartAmount = -$PartAmount;
	return $CustomerFee * $PartAmount * pow((1 + $CustomerFee), $PayCount) / (1 - pow((1 + $CustomerFee), $PayCount)); 
} 
function roundUp($number, $digits){
	$factor = pow(10,$digits);
	return ceil($number*$factor) / $factor;
}
function ComputeFee($PartAmount, $CustomerFeePercent, $PayCount, $YearMonths){
		
	return ((($PartAmount*$CustomerFeePercent/$YearMonths*( pow((1+($CustomerFeePercent/$YearMonths)),$PayCount)))/
		((pow((1+($CustomerFeePercent/$YearMonths)),$PayCount))-1))*$PayCount)-$PartAmount;
}

//....................

function ComputePartPayments(){
	
	$obj = new LON_ReqParts($_REQUEST["PartID"]);
	
	$YearMonth = 12;
	if($obj->IntervalType == "DAY")
		$YearMonths = floor(365/$obj->PayInterval);
	
	$FirstPay = roundUp( PMT($obj->CustomerFee, $obj->PayCount, $obj->PartAmount, $YearMonth) );
	$TotalFee = round(ComputeFee($obj->PartAmount, $obj->CustomerFee/100, $obj->PayCount, $YearMonths));
	$LastPay = $obj->PartAmount*1 + $TotalFee - $FirstPay*($obj->PayCount-1);
	
	$netPartPaments = round($obj->PartAmount/$obj->PayCount);
	$lastNetPayment = $obj->PartAmount*1 - $netPartPaments*($obj->PayCount-1);
	
	for($i=0; $i < $obj->PayCount-1; $i++)
	{
		$obj2 = new LON_PartPayments();
		$obj2->PartID = $obj->PartID;
		$obj2->PayAmount = $netPartPaments;
		$obj2->FeeAmount = $FirstPay - $netPartPaments;
		$obj2->FeePercent = $obj->CustomerFee;
	}
}

?>
