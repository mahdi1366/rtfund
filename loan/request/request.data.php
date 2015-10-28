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
		
	case "SavePartPayment":
		SavePartPayment();
		
	case "PayPart":
		PayPart();
		
	//----------------------------------------------
		
	case "GetLastFundComment":
		GetLastFundComment();
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

function ChangeStatus($RequestID, $StatusID, $StepComment = "", $LogOnly = false, $pdo = null){
	
	if(!$LogOnly)
	{
		$obj = new LON_requests();
		$obj->RequestID = $_POST["RequestID"];
		$obj->StatusID = $StatusID;
		if(!$obj->EditRequest($pdo))
			return false;
	}
	PdoDataAccess::runquery("insert into LON_ReqFlow(RequestID,PersonID,StatusID,ActDate,StepComment) 
		values(?,?,?,now(),?)", array(
			$RequestID,
			$_SESSION["USER"]["PersonID"],
			$StatusID,
			$StepComment
		), $pdo);
}

function ChangeRequesrStatus(){
	
	$result = ChangeStatus($_POST["RequestID"],$_POST["StatusID"],$_POST["StepComment"]);
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

function PayPart(){
	
	$pdo = PdoDataAccess::getPdoObject();
	$pdo->beginTransaction();
	
	$PartID = $_POST["PartID"];
	$partobj = new LON_ReqParts($PartID);
	$partobj->IsPayed = "YES";
	$partobj->EditPart($pdo);
	ChangeStatus($partobj->RequestID, "80", $partobj->PartDesc, false, $pdo);
	
	$ReqObj = new LON_requests($partobj->RequestID);
	
	RegisterPayPartDoc($ReqObj, $partobj, $pdo);
	
	
}

//------------------------------------------------

function GetPartPayments(){
	
	$dt = LON_PartPayments::SelectAll("PartID=?", array($_REQUEST["PartID"]));
	echo dataReader::getJsonData($dt, count($dt), $_GET["callback"]);
	die();
}

//....................
function PMT($CustomerWage, $PayCount, $PartAmount, $YearMonths) {  
	$CustomerWage = $CustomerWage/($YearMonths*100);
	$PartAmount = -$PartAmount;
	return $CustomerWage * $PartAmount * pow((1 + $CustomerWage), $PayCount) / (1 - pow((1 + $CustomerWage), $PayCount)); 
} 
function roundUp($number, $digits){
	$factor = pow(10,$digits);
	return ceil($number*$factor) / $factor;
}
function ComputeWage($PartAmount, $CustomerWagePercent, $PayCount, $YearMonths){
		
	return ((($PartAmount*$CustomerWagePercent/$YearMonths*( pow((1+($CustomerWagePercent/$YearMonths)),$PayCount)))/
		((pow((1+($CustomerWagePercent/$YearMonths)),$PayCount))-1))*$PayCount)-$PartAmount;
}
function AddToJDate($jdate, $day=0, $month=0) {

	if($day == 0)
	{
		$jdate_array = preg_split('/[\-\/]/',$jdate);
		$year = $jdate_array[1]*1+$month > 12 ? $jdate_array[0]*1+1 : $jdate_array[0];
		$month = $jdate_array[1]*1+$month > 12 ? $jdate_array[1]*1-12+$month : $jdate_array[1]*1+$month;
		$day = $jdate_array[2];
		return $year . "-" . $month . "-" . $day;
	}
	return DateModules::AddToJDate($jdate, $day);
}
//....................

function ComputePartPayments(){
	
	$obj = new LON_ReqParts($_REQUEST["PartID"]);
	
	PdoDataAccess::runquery("delete from LON_PartPayments where PartID=?", array($obj->PartID));
	
	$YearMonths = 12;
	if($obj->IntervalType == "DAY")
		$YearMonths = floor(365/$obj->PayInterval);
	
	$allPay = roundUp( PMT($obj->CustomerWage, $obj->PayCount, $obj->PartAmount, $YearMonths), -3);
	$TotalWage = round(ComputeWage($obj->PartAmount, $obj->CustomerWage/100, $obj->PayCount, $YearMonths));
	$LastPay = $obj->PartAmount*1 + $TotalWage - $allPay*($obj->PayCount-1);
	$netPartPaments = round($obj->PartAmount/$obj->PayCount);
	$lastNetPayment = $obj->PartAmount*1 - $netPartPaments*($obj->PayCount-1);
	
	$gdate = DateModules::miladi_to_shamsi($obj->PayDate);
	
	$pdo = PdoDataAccess::getPdoObject();
	$pdo->beginTransaction();
	
	for($i=0; $i < $obj->PayCount-1; $i++)
	{
		$obj2 = new LON_PartPayments();
		
		$obj2->PayDate = AddToJDate($gdate, 
			$obj->IntervalType == "DAY" ? $obj->PayInterval*($i+1) : 0, 
			$obj->IntervalType == "MONTH" ? $obj->PayInterval*($i+1) : 0);
		$obj2->PartID = $obj->PartID;
		$obj2->PayAmount = $netPartPaments;
		$obj2->WageAmount = $allPay - $netPartPaments;
		$obj2->CustomerWage = $obj->CustomerWage;
		$obj2->FundWage = $obj->FundWage;
		
		if(!$obj2->AddPartPayment($pdo))
		{
			$pdo->rollBack();
			echo Response::createObjectiveResponse(false, "");
			die();
		}
	}
	
	$obj2 = new LON_PartPayments();
		
	$obj2->PayDate = AddToJDate($gdate, 
		$obj->IntervalType == "DAY" ? $obj->PayInterval*($obj->PayCount) : 0, 
		$obj->IntervalType == "MONTH" ? $obj->PayInterval*($obj->PayCount) : 0);
	$obj2->PartID = $obj->PartID;
	$obj2->PayAmount = $lastNetPayment;
	$obj2->WageAmount = $LastPay - $lastNetPayment;
	$obj2->CustomerWage = $obj->CustomerWage;
	$obj2->FundWage = $obj->FundWage;

	if(!$obj2->AddPartPayment($pdo))
	{
		$pdo->rollBack();
		echo Response::createObjectiveResponse(false, "");
		die();
	}
	
	$pdo->commit();
	echo Response::createObjectiveResponse(true, "");
	die();
}

function SavePartPayment(){
	
	$obj = new LON_PartPayments();
	PdoDataAccess::FillObjectByJsonData($obj, $_POST["record"]);
	
	$result = $obj->EditPartPayment();
	echo Response::createObjectiveResponse($result, "");
	die();
}

//-------------------------------------------------

function GetLastFundComment(){
	
	if(empty($_POST["RequestID"]))
	{
		echo Response::createObjectiveResponse(true, $comment);
		die();
	}
	$RequestID = $_POST["RequestID"];
	
	$dt = PdoDataAccess::runquery("select * from LON_ReqFlow 
		where RequestID=? AND StatusID=60 order by FlowID desc", array($RequestID));
	
	$comment = count($dt)>0 ? $dt[0]["StepComment"] : '';
	echo Response::createObjectiveResponse(true, $comment);
	die();
}

?>
