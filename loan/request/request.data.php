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
		
	case "Selectguarantees":
		Selectguarantees();
		
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
		
	case "GetPartInstallments":
		GetPartInstallments();
		
	case "ComputePartPayments":
		ComputePartPayments();
		
	case "SavePartPayment":
		SavePartPayment();
		
	case "PayPart":
		PayPart();
		
	//----------------------------------------------
		
	case "GetLastFundComment":
		GetLastFundComment();
		
	//-----------------------------------------------
		
	case "selectMyParts":
		selectMyParts();
		
}

function SaveLoanRequest(){
	
	$obj = new LON_requests();
	PdoDataAccess::FillObjectByArray($obj, $_POST);
	
	if(isset($_POST["sending"]) &&  $_POST["sending"] == "true")
		$obj->StatusID = 10;
	
	$obj->AgentGuarantee = isset($_POST["AgentGuarantee"]) ? "YES" : "NO";	

	$obj->guarantees = array();
	$arr = array_keys($_POST);
	foreach($arr as $index)
		if(strpos($index, "guarantee") !== false)
			$obj->guarantees[] = str_replace("guarantee_", "", $index);
	$obj->guarantees = implode(",", $obj->guarantees);
	
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

function Selectguarantees(){
	
	$temp = PdoDataAccess::runquery("select * from BaseInfo where TypeID=8 and param1=1");
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
		$obj->RequestID = $RequestID;
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
	
	require_once '../../accounting/docs/import.data.php';
	RegisterPayPartDoc($ReqObj, $partobj, $pdo);
	die();
}

//------------------------------------------------

function GetPartInstallments(){
	
	$dt = LON_installments::SelectAll("PartID=? " . dataReader::makeOrder() , array($_REQUEST["PartID"]));
	
	for($i=0; $i < count($dt); $i++)
	{
		if($dt["instal"])
		$dt[$i]["ForfeitAmount"] = 0;
		
	}
	
	echo dataReader::getJsonData($dt, count($dt), $_GET["callback"]);
	die();
}

//....................
function PMT($CustomerWage, $InstallmentCount, $PartAmount, $YearMonths) {  
	$CustomerWage = $CustomerWage/($YearMonths*100);
	$PartAmount = -$PartAmount;
	return $CustomerWage * $PartAmount * pow((1 + $CustomerWage), $InstallmentCount) / (1 - pow((1 + $CustomerWage), $InstallmentCount)); 
} 
function ComputeWage($PartAmount, $CustomerWagePercent, $InstallmentCount, $YearMonths){
		
	return ((($PartAmount*$CustomerWagePercent/$YearMonths*( pow((1+($CustomerWagePercent/$YearMonths)),$InstallmentCount)))/
		((pow((1+($CustomerWagePercent/$YearMonths)),$InstallmentCount))-1))*$InstallmentCount)-$PartAmount;
}
function roundUp($number, $digits){
	$factor = pow(10,$digits);
	return ceil($number*$factor) / $factor;
}
function YearWageCompute($PartObj, $TotalWage, $yearNo, $YearMonths){
		
	$PayMonth = preg_split('/\//',DateModules::MiladiToShamsi($PartObj->PartDate));
	$PayMonth = $PayMonth[1]*1;
	$PayMonth = $PayMonth*$YearMonths/12;
	
	$FirstYearInstallmentCount = $YearMonths - $PayMonth;
	$MidYearInstallmentCount = Math.floor(($PartObj->InstallmentCount-$FirstYearInstallmentCount) / $YearMonths);
	$LastYeatInstallmentCount = ($PartObj->InstallmentCount-$FirstYearInstallmentCount) % $YearMonths;

	if($yearNo > $MidYearInstallmentCount+2)
		return 0;

	$F9 = $PartObj->InstallmentCount*1;
	$BeforeMonths = 0;
	if($yearNo == 2)
		$BeforeMonths = $FirstYearInstallmentCount;
	else if($yearNo > 2)
		$BeforeMonths = $FirstYearInstallmentCount + ($yearNo-2)*$YearMonths;

	$curMonths = $FirstYearInstallmentCount;
	if($yearNo > 1 && $yearNo <= $MidYearInstallmentCount+1)
		$curMonths = $YearMonths;
	else if($yearNo > $MidYearInstallmentCount+1)
		$curMonths = $LastYeatInstallmentCount;

	$val = (((($F9-$BeforeMonths)*($F9-$BeforeMonths+1))-
		($F9-$BeforeMonths-$curMonths)*($F9-$BeforeMonths-$curMonths+1)))/($F9*($F9+1))*$TotalWage;
	return $val;
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
	
	PdoDataAccess::runquery("delete from LON_installments where PartID=?", array($obj->PartID));
	
	$YearMonths = 12;
	if($obj->IntervalType == "DAY")
		$YearMonths = floor(365/$obj->PayInterval);
	
	$allPay = roundUp( PMT($obj->CustomerWage, $obj->InstallmentCount, $obj->PartAmount, $YearMonths), -3);
	$TotalWage = round(ComputeWage($obj->PartAmount, $obj->CustomerWage/100, $obj->InstallmentCount, $YearMonths));
	$LastPay = $obj->PartAmount*1 + $TotalWage - $allPay*($obj->InstallmentCount-1);
	$PureInstallmentAmount = round($obj->PartAmount/$obj->InstallmentCount);
	$lastInstallmentAmount = $obj->PartAmount*1 - $PureInstallmentAmount*($obj->InstallmentCount-1);
	
	$gdate = DateModules::miladi_to_shamsi($obj->PartDate);
	
	$pdo = PdoDataAccess::getPdoObject();
	$pdo->beginTransaction();
	
	for($i=0; $i < $obj->InstallmentCount-1; $i++)
	{
		$obj2 = new LON_installments();
		
		$obj2->InstallmentDate = AddToJDate($gdate, 
			$obj->IntervalType == "DAY" ? $obj->PayInterval*($i+1) : 0, 
			$obj->IntervalType == "MONTH" ? $obj->PayInterval*($i+1) : 0);
		$obj2->PartID = $obj->PartID;
		$obj2->InstallmentAmount = $PureInstallmentAmount;
		$obj2->WageAmount = $allPay - $PureInstallmentAmount;
		$obj2->CustomerWage = $obj->CustomerWage;
		$obj2->FundWage = $obj->FundWage;
		
		if(!$obj2->AddPartPayment($pdo))
		{
			$pdo->rollBack();
			echo Response::createObjectiveResponse(false, "");
			die();
		}
	}
	
	$obj2 = new LON_installments();
		
	$obj2->InstallmentDate = AddToJDate($gdate, 
		$obj->IntervalType == "DAY" ? $obj->PayInterval*($obj->InstallmentCount) : 0, 
		$obj->IntervalType == "MONTH" ? $obj->PayInterval*($obj->InstallmentCount) : 0);
	$obj2->PartID = $obj->PartID;
	$obj2->InstallmentAmount = $lastInstallmentAmount;
	$obj2->WageAmount = $LastPay - $lastInstallmentAmount;
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
	
	$obj = new LON_installments();
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

//-------------------------------------------------

function selectMyParts(){
	
	$query = "select * 
		from LON_ReqParts p
		join LON_requests using(RequestID)
		join LON_installments using(PartID)
		where LoanPersonID=? 
		Group by p.PartID";
	$dt = PdoDataAccess::runquery($query, array($_SESSION["USER"]["PersonID"]));
	echo dataReader::getJsonData($dt, count($dt), $_GET["callback"]);
	die();
}

?>
