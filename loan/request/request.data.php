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
require_once "../../office/workflow/wfm.class.php";

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
		
	case "ComputeInstallments":
		ComputeInstallments();
		
	case "SavePartPayment":
		SavePartPayment();
		
	case "PayPart":
		PayPart();
		
	case "ReturnPayPart":
		ReturnPayPart();
		
	case "EndPart":
		EndPart();
		
	case "StartFlow":
		StartFlow();
	//----------------------------------------------
		
	case "GetLastFundComment":
		GetLastFundComment();
		
	//-----------------------------------------------
		
	case "selectMyParts":
		selectMyParts();
		
	case "SelectReadyToPayParts":
		SelectReadyToPayParts();
		
}

function SaveLoanRequest(){
	
	$obj = new LON_requests();
	PdoDataAccess::FillObjectByArray($obj, $_POST);
	
	$obj->AgentGuarantee = isset($_POST["AgentGuarantee"]) ? "YES" : "NO";	

	$obj->guarantees = array();
	$arr = array_keys($_POST);
	foreach($arr as $index)
		if(strpos($index, "guarantee") !== false)
			$obj->guarantees[] = str_replace("guarantee_", "", $index);
	$obj->guarantees = implode(",", $obj->guarantees);
	
	//------------------------------------------------------
	if($_SESSION["USER"]["IsCustomer"] == "YES")
	{
		$obj->LoanPersonID = $_SESSION["USER"]["PersonID"];
		$obj->StatusID = 10;
	}
	if($_SESSION["USER"]["IsAgent"] == "YES")
	{
		if(isset($_POST["sending"]) &&  $_POST["sending"] == "true")
			$obj->StatusID = 10;
		else
			$obj->StatusID = 1;
		
		$obj->LoanID = Default_Agent_Loan;
	}
	if($_SESSION["USER"]["IsStaff"] == "YES")
	{
		$obj->LoanID = Default_Agent_Loan;
	}
	//-
	//------------------------------------------------------
	
	if(empty($obj->RequestID))
	{
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
	
	if($_SESSION["USER"]["IsCustomer"] == "YES" && $obj->LoanID > 0)
	{
		$loanObj = new LON_loans($obj->LoanID);
		$PartObj = new LON_ReqParts();
		PdoDataAccess::FillObjectByObject($loanObj, $PartObj);
		$PartObj->RequestID = $obj->RequestID;
		$PartObj->PartDesc = "مرحله اول";
		$PartObj->FundWage = $loanObj->CustomerWage;
		$PartObj->PartAmount = $obj->ReqAmount;
		$PartObj->PartDate = PDONOW;
		$PartObj->AddPart();
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
	$count = $dt->rowCount();
	$dt = PdoDataAccess::fetchAll($dt, $_GET["start"], $_GET["limit"]);
	//print_r(ExceptionHandler::PopAllExceptions());
	echo dataReader::getJsonData($dt, $count, $_GET["callback"]);
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
	else if($_SESSION["USER"]["IsStaff"] == "YES")
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
	
	if (isset($_REQUEST['fields']) && isset($_REQUEST['query'])) {
        $field = $_REQUEST['fields'];
		$field = $field == "ReqFullname" ? "concat_ws(' ',p1.fname,p1.lname,p1.CompanyName)" : $field;
		$field = $field == "LoanFullname" ? "concat_ws(' ',p2.fname,p2.lname,p2.CompanyName)" : $field;
        $where .= ' and ' . $field . ' like :fld';
        $param[':fld'] = '%' . $_REQUEST['query'] . '%';
    }
	
	$where .= dataReader::makeOrder();
	$dt = LON_requests::SelectAll($where, $param);
	//print_r(ExceptionHandler::PopAllExceptions());
	$count = $dt->rowCount();
	$dt = PdoDataAccess::fetchAll($dt, $_GET["start"], $_GET["limit"]);	
	echo dataReader::getJsonData($dt, $count, $_GET["callback"]);
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
	
	for($i=0; $i < count($dt);$i++)
	{
		$temp = PdoDataAccess::runquery("select DocStatus 
			from ACC_DocItems join ACC_docs using(DocID) where SourceType='PAY_LOAN_PART' AND 
			SourceID=? AND SourceID2=?", array($dt[$i]["RequestID"], $dt[$i]["PartID"]));
		$dt[$i]["DocStatus"] = count($temp) == 0 ? "" : $temp[0]["DocStatus"];
		
		$dt[$i]["IsStarted"] = WFM_FlowRows::IsFlowStarted(1, $dt[$i]["PartID"]) ? "YES" : "NO";
		$dt[$i]["IsEnded"] = WFM_FlowRows::IsFlowEnded(1, $dt[$i]["PartID"]) ? "YES" : "NO";
		
		$temp = LON_installments::SelectAll("IsPaid='NO' AND PartID=?", array($dt[$i]["PartID"]));
		$dt[$i]["IsPartEnded"] = count($temp) == 0 ? "YES" : "NO";
	}
	
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
	ChangeStatus($partobj->RequestID, "80", $partobj->PartDesc, true, $pdo);
	
	$ReqObj = new LON_requests($partobj->RequestID);
	
	require_once '../../accounting/docs/import.data.php';
	$result = RegisterPayPartDoc($ReqObj, $partobj, $pdo);
	
	if(!$result)
	{
		$pdo->rollBack();
	}
	else
		$pdo->commit();
	
	echo Response::createObjectiveResponse($result, !$result ? PdoDataAccess::GetExceptionsToString() : "");
	die();
}

function ReturnPayPart(){
	
	$pdo = PdoDataAccess::getPdoObject();
	$pdo->beginTransaction();
	
	$PartID = $_POST["PartID"];
	$partobj = new LON_ReqParts($PartID);
	
	//------------- check for Acc doc confirm -------------------
	$temp = PdoDataAccess::runquery("select DocStatus 
		from ACC_DocItems join ACC_docs using(DocID) where SourceType='PAY_LOAN_PART' AND 
		SourceID=? AND SourceID2=?", array($partobj->RequestID, $partobj->PartID));
	if(count($temp) > 0 && $temp[0]["DocStatus"] != "RAW")
	{
		echo Response::createObjectiveResponse(false, "سند حسابداری این مرحله تایید شده است. و قادر به برگشت نمی باشید");
		die();

	}
	//-----------------------------------------------------------
	$partobj->IsPayed = "NO";
	$partobj->EditPart($pdo);
	ChangeStatus($partobj->RequestID, "90", $partobj->PartDesc, true, $pdo);
	
	require_once '../../accounting/docs/import.data.php';
	$result = ReturnPayPartDoc($partobj, $pdo);
	
	if(!$result)
	{
		$pdo->rollBack();
	}
	else
		$pdo->commit();
	
	echo Response::createObjectiveResponse($result, !$result ? PdoDataAccess::GetExceptionsToString() : "");
	die();
}

function EndPart(){
	
	$PartID = $_POST["PartID"];
	
	$dt = LON_installments::SelectAll("IsPaid='YES' AND PartID=?", array($PartID));
	if(count($dt) == 0)
	{
		echo Response::createObjectiveResponse(false, "هیچ قسطی از این مرحله پرداخت نگردیده است.");
		die();
	}
	$dt = LON_installments::SelectAll("IsPaid='NO' AND PartID=?", array($PartID));
	if(count($dt) == 0)
	{
		echo Response::createObjectiveResponse(false, "کلیه اقساط این مرحله پرداخت گردیده و مرحله پایان یافته است");
		die();
	}
	
	$pdo = PdoDataAccess::getPdoObject();
	$pdo->beginTransaction();
	
	$partobj = new LON_ReqParts($PartID);
	ChangeStatus($partobj->RequestID, "100", $partobj->PartDesc, true, $pdo);
	
	//---------------- delete not paid installments ---------------
	PdoDataAccess::runquery("delete from LON_installments where PartID=? AND IsPaid='NO'",
		array($partobj->PartID), $pdo);
	//--------------- get total paid amount -----------------------
	$dt = PdoDataAccess::runquery("select sum(PaidAmount) sumAmount, count(*) sumCount 
		from LON_installments where IsPaid='YES' AND PartID=?", array($partobj->PartID), $pdo);
	$PaidAmount = $dt[0]["sumAmount"]*1;
	$installmentCount = $dt[0]["sumCount"]*1;
	
	$RemainAmount = $partobj->PartAmount - $PaidAmount;
	$remainInstallments = $partobj->InstallmentCount - $installmentCount;
	//-------------- register doc  ----------------
	$ReqObj = new LON_requests($partobj->RequestID);
	require_once '../../accounting/docs/import.data.php';
	if(!EndPartDoc($ReqObj, $partobj, $PaidAmount, $installmentCount, $pdo))
	{
		$pdo->rollBack();
		echo Response::createObjectiveResponse(false, "خطا در صدور سند حسابداری");
		die();
	}
	
	//---------------- edit current part info -------------------
	$partobj->PartAmount = $PaidAmount;
	$partobj->InstallmentCount = $installmentCount;
	if(!$partobj->EditPart($pdo))
	{
		$pdo->rollBack();
		echo Response::createObjectiveResponse(false, "خطا در ویرایش مرحله");
		die();
	}
	
	//-------------- add new part with remain amount --------------
	$newObj = new LON_ReqParts();
	PdoDataAccess::FillObjectByObject($partobj, $newObj);
	unset($newObj->PartID);
	$newObj->PartDesc = $partobj->PartDesc . "[نسخه جدید]";
	$newObj->PartDate = PDONOW;
	$newObj->IsPayed = "NO";
	$newObj->PartAmount = $RemainAmount;
	$newObj->InstallmentCount = $remainInstallments;
	if(!$newObj->AddPart($pdo))
	{
		$pdo->rollBack();
		print_r($newObj);
		print_r(ExceptionHandler::PopAllExceptions());
		echo Response::createObjectiveResponse(false, "خطا در ایجاد مرحله جدید");
		die();
	}
	$pdo->commit();
	echo Response::createObjectiveResponse(true, "");
	die();
}

function StartFlow(){
	
	$PartID = $_REQUEST["PartID"];
	$result = WFM_FlowRows::StartFlow(1, $PartID);
	echo Response::createObjectiveResponse($result, "");
	die();
}
//------------------------------------------------

function GetPartInstallments(){
	
	$dt = LON_installments::SelectAll("PartID=? " . dataReader::makeOrder() , array($_REQUEST["PartID"]));
	for($i=0; $i < count($dt); $i++)
	{
		$dt[$i]["ForfeitAmount"] = 0;
		if ($dt[$i]["InstallmentDate"] < DateModules::Now()) {
			$forfeitDays = DateModules::GDateMinusGDate(DateModules::Now(),$dt[$i]["InstallmentDate"]);
			$dt[$i]["ForfeitAmount"] = $dt[$i]["InstallmentAmount"]*$dt[$i]["ForfeitPercent"]*$forfeitDays/100;
		}
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
		
	$PayMonth = preg_split('/\//',DateModules::miladi_to_shamsi($PartObj->PartDate));
	$PayMonth = $PayMonth[1]*1;
	$PayMonth = $PayMonth*$YearMonths/12;
	
	$FirstYearInstallmentCount = $YearMonths - $PayMonth;
	$MidYearInstallmentCount = floor(($PartObj->InstallmentCount-$FirstYearInstallmentCount) / $YearMonths);
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

function ComputeInstallments(){
	
	$obj = new LON_ReqParts($_REQUEST["PartID"]);
	
	PdoDataAccess::runquery("delete from LON_installments where PartID=?", array($obj->PartID));
	
	$YearMonths = 12;
	if($obj->IntervalType == "DAY")
		$YearMonths = floor(365/$obj->PayInterval);
	
	$allPay = roundUp( PMT($obj->CustomerWage, $obj->InstallmentCount, $obj->PartAmount, $YearMonths), -3);
	$TotalWage = round(ComputeWage($obj->PartAmount, $obj->CustomerWage/100, $obj->InstallmentCount, $YearMonths));
	$LastPay = $obj->PartAmount*1 + $TotalWage - $allPay*($obj->InstallmentCount-1);
	
	$jdate = DateModules::miladi_to_shamsi($obj->PartDate);
	$jdate = AddToJDate($jdate, 0, $obj->DelayMonths);
	
	$pdo = PdoDataAccess::getPdoObject();
	$pdo->beginTransaction();
	
	for($i=0; $i < $obj->InstallmentCount-1; $i++)
	{
		$obj2 = new LON_installments();
		
		$obj2->InstallmentDate = AddToJDate($jdate, 
			$obj->IntervalType == "DAY" ? $obj->PayInterval*($i+1) : 0, 
			$obj->IntervalType == "MONTH" ? $obj->PayInterval*($i+1) : 0);
		$obj2->PartID = $obj->PartID;
		$obj2->InstallmentAmount = $allPay;
		
		if(!$obj2->AddInstallment($pdo))
		{
			$pdo->rollBack();
			echo Response::createObjectiveResponse(false, "");
			die();
		}
	}
	
	$obj2 = new LON_installments();
		
	$obj2->InstallmentDate = AddToJDate($jdate, 
		$obj->IntervalType == "DAY" ? $obj->PayInterval*($obj->InstallmentCount) : 0, 
		$obj->IntervalType == "MONTH" ? $obj->PayInterval*($obj->InstallmentCount) : 0);
	$obj2->PartID = $obj->PartID;
	$obj2->InstallmentAmount = $LastPay;

	if(!$obj2->AddInstallment($pdo))
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
	
	$result = $obj->EditInstallment();
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

function SelectReadyToPayParts(){
	
	$dt = PdoDataAccess::runquery("select max(StepID) from WFM_FlowSteps where FlowID=1");

	$dt = PdoDataAccess::runquery("
		select RequestID,PartAmount,PartDesc,PartDate,
			if(p1.IsReal='YES',concat(p1.fname, ' ', p1.lname),p1.CompanyName) ReqFullname,
			if(p2.IsReal='YES',concat(p2.fname, ' ', p2.lname),p2.CompanyName) LoanFullname
			
		from WFM_FlowRows 
		join LON_ReqParts on(PartID=ObjectID)
		join LON_requests r using(RequestID)
		join BSC_persons p1 on(p1.PersonID=r.ReqPersonID)
		left join BSC_persons p2 on(p2.PersonID=r.LoanPersonID)
		left join ACC_DocItems di on(SourceType='PAY_LOAN_PART' AND SourceID=RequestID AND SourceID2=PartID)
		where FlowID=1 AND StepID=? AND ActionType='CONFIRM' AND di.ItemID is null",
		array($dt[0][0]));

	echo dataReader::getJsonData($dt, count($dt), $_GET["callback"]);
	die();
}

?>
