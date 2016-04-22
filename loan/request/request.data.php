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
require_once '../../accounting/definitions.inc.php';
require_once '../../accounting/docs/import.data.php';

$task = isset($_REQUEST["task"]) ? $_REQUEST["task"] : "";
if(!empty($task))
{
	$task();
}
//....................
function PMT($CustomerWage, $InstallmentCount, $PartAmount, $YearMonths, $PayInterval) {  
	
	if($CustomerWage == 0)
		return $PartAmount/$InstallmentCount;
	
	if($PayInterval == 0)
		return $PartAmount;
	
	$CustomerWage = $CustomerWage/($YearMonths*100);
	$PartAmount = -$PartAmount;
	return $CustomerWage * $PartAmount * pow((1 + $CustomerWage), $InstallmentCount) / (1 - pow((1 + $CustomerWage), $InstallmentCount)); 
} 
function ComputeWage($PartAmount, $CustomerWagePercent, $InstallmentCount, $YearMonths, $PayInterval){
	
	if($PayInterval == 0)
		return 0;
	
	if($CustomerWagePercent == 0)
		return 0;
	
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
//....................

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
	if($_SESSION["USER"]["IsAgent"] == "YES" || $_SESSION["USER"]["IsSupporter"] == "YES")
	{
		if(empty($_POST["ReqPersonID"]))
			$obj->ReqPersonID = $_SESSION["USER"]["PersonID"];
		
		if(isset($_POST["sending"]) &&  $_POST["sending"] == "true")
			$obj->StatusID = 10;
		else
			$obj->StatusID = 1;
		
		$obj->LoanID = Default_Agent_Loan;
	}
	else if($_SESSION["USER"]["IsStaff"] == "YES")
	{
		$obj->LoanID = Default_Agent_Loan;
		if(empty($obj->RequestID) && isset($_SESSION["USER"]["framework"]))
			$obj->StatusID = 1;
	}
	else if($_SESSION["USER"]["IsCustomer"] == "YES")
	{
		if(!isset($obj->LoanPersonID))
			$obj->LoanPersonID = $_SESSION["USER"]["PersonID"];
		$obj->StatusID = 10;
	}
	//------------------------------------------------------
	if(empty($obj->RequestID))
	{
		$obj->AgentGuarantee = isset($_POST["AgentGuarantee"]) ? "YES" : "NO";
		$result = $obj->AddRequest();
		if($result)
			ChangeStatus($obj->RequestID,$obj->StatusID, "", true);
		
		$loanObj = new LON_loans($obj->LoanID);
		$PartObj = new LON_ReqParts();
		PdoDataAccess::FillObjectByObject($loanObj, $PartObj);
		$PartObj->RequestID = $obj->RequestID;
		$PartObj->PartDesc = "فاز اول";
		$PartObj->FundWage = $loanObj->CustomerWage;
		$PartObj->PartAmount = $obj->ReqAmount;
		$PartObj->PartDate = PDONOW;
		$PartObj->AddPart();		
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
	$param = array();
	if (isset($_REQUEST['fields']) && isset($_REQUEST['query'])) {
        $field = $_REQUEST['fields'];
		$field = $field == "ReqFullname" ? "concat_ws(' ',p1.fname,p1.lname,p1.CompanyName)" : $field;
		$field = $field == "LoanFullname" ? "concat_ws(' ',p2.fname,p2.lname,p2.CompanyName)" : $field;
        $where .= ' and ' . $field . ' like :fld';
        $param[':fld'] = '%' . $_REQUEST['query'] . '%';
    }
	
	$dt = LON_requests::SelectAll($where . dataReader::makeOrder(), $param);
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
	
	
	if(!empty($_REQUEST["IsEnded"]))
	{
		$where .= " AND IsEnded = :e "; 
		$param[":e"] = $_REQUEST["IsEnded"];
	}
	
	$where .= dataReader::makeOrder();
	$dt = LON_requests::SelectAll($where, $param);
	//print_r(ExceptionHandler::PopAllExceptions());
	//echo PdoDataAccess::GetLatestQueryString();
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
	echo Response::createObjectiveResponse($res, !$res ? ExceptionHandler::GetExceptionsToString() : "");
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
	
	return ExceptionHandler::GetExceptionCount() == 0;
}

function ChangeRequestStatus(){
	
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
	
	$CostCode_commitment = 165; // 200-05
	for($i=0; $i < count($dt);$i++)
	{
		$temp = PdoDataAccess::runquery("select ifnull(sum(CreditorAmount),0)
			from ACC_DocItems join ACC_docs using(DocID) where 
			 CostID=? AND SourceType=" . DOCTYPE_LOAN_PAYMENT . " AND 
			SourceID=? AND SourceID2=? AND DocStatus in('CONFIRM','ARCHIVE')", 
			array($CostCode_commitment, $dt[$i]["RequestID"], $dt[$i]["PartID"]));
		$dt[$i]["IsPaid"] = $temp[0][0] == $dt[$i]["PartAmount"] ? "YES" : "NO"; 		
		
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
		echo Response::createObjectiveResponse(false, "خطا در ثبت فاز");
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
	
	$PayAmount = $_POST["PayAmount"];
	
	$pdo = PdoDataAccess::getPdoObject();
	$pdo->beginTransaction();
	
	$PartID = $_POST["PartID"];
	$partobj = new LON_ReqParts($PartID);

	ChangeStatus($partobj->RequestID, "80", "پرداخت مبلغ " . number_format($PayAmount) . " از " . 
			$partobj->PartDesc, true, $pdo);
	
	$ReqObj = new LON_requests($partobj->RequestID);
	
	if($partobj->MaxFundWage*1 > 0)
		$partobj->MaxFundWage = round($partobj->MaxFundWage*$PayAmount/$partobj->PartAmount);
	
	$result = RegisterPayPartDoc($ReqObj, $partobj, $PayAmount*1, $pdo);
	//print_r(ExceptionHandler::PopAllExceptions());
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
	
	if(empty($_POST["PartID"]) || empty($_POST["DocID"]))
	{
		echo Response::createObjectiveResponse(false, "کد سند یا کد فاز نامعتبر");
		die();
	}
	
	$PartID = $_POST["PartID"];
	$partobj = new LON_ReqParts($PartID);
	$DocID = $_POST["DocID"];
	
	if(!($partobj->PartID >0 && $DocID > 0))
	{
		echo Response::createObjectiveResponse(false, "فاز مربوطه یافت نشد");
		die();
	}
	
	//------------- check for Acc doc confirm -------------------
	$temp = PdoDataAccess::runquery("select DocStatus 
		from ACC_DocItems join ACC_docs using(DocID) where SourceType=" . DOCTYPE_LOAN_PAYMENT . " AND 
		SourceID=? AND SourceID2=? AND DocID=?", 
		array($partobj->RequestID, $partobj->PartID, $DocID));
	if(count($temp) == 0)
	{
		echo Response::createObjectiveResponse(false, "سند مربوطه یافت نشد");
		die();
	}
	if(count($temp) > 0 && $temp[0]["DocStatus"] != "RAW")
	{
		echo Response::createObjectiveResponse(false, "سند حسابداری این فاز تایید شده است. و قادر به برگشت نمی باشید");
		die();
	}
	//------- check for being first doc and there excists docs after -----------
	$CostCode_todiee = COSTID_Todiee;
	$temp = PdoDataAccess::runquery("select * from ACC_DocItems 
		where CostID=? AND CreditorAmount>0 AND DocID=?",
		array($CostCode_todiee, $DocID));
	if(count($temp) > 0)
	{
		$dt = PdoDataAccess::runquery("select * from ACC_DocItems where CostID=? AND DebtorAmount>0 
			AND SourceType=? AND SourceID2=?",
			array($CostCode_todiee, DOCTYPE_LOAN_PAYMENT, $PartID));
		if(count($dt) > 0)
		{
			echo Response::createObjectiveResponse(false, "به دلیل اینکه این سند اولین سند پرداخت می باشد و بعد از آن اسناد پرداخت دیگری صادر شده است" . 
				" قادر به برگشت نمی باشید. <br> برای برگشت ابتدا کلیه اسناد بعدی را برگشت بزنید");
			die();
		}
	}
	//-----------------------------------------------------------
	$result = ReturnPayPartDoc($DocID);
	
	if($result)
		ChangeStatus($partobj->RequestID, "90", $partobj->PartDesc, true);
		
	//print_r(ExceptionHandler::PopAllExceptions());
	echo Response::createObjectiveResponse($result, !$result ? PdoDataAccess::GetExceptionsToString() : "");
	die();
}

function EndPart(){
	
	$PartID = $_POST["PartID"];
	
	$dt = LON_installments::SelectAll("IsPaid='YES' AND PartID=?", array($PartID));
	if(count($dt) == 0)
	{
		echo Response::createObjectiveResponse(false, "هیچ قسطی از این فاز پرداخت نگردیده است.");
		die();
	}
	$dt = LON_installments::SelectAll("IsPaid='NO' AND PartID=?", array($PartID));
	if(count($dt) == 0)
	{
		echo Response::createObjectiveResponse(false, "کلیه اقساط این فاز پرداخت گردیده و فاز پایان یافته است");
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
		echo Response::createObjectiveResponse(false, "خطا در ویرایش فاز");
		die();
	}
	
	//-------------- add new part with remain amount --------------
	$newObj = new LON_ReqParts();
	PdoDataAccess::FillObjectByObject($partobj, $newObj);
	unset($newObj->PartID);
	$newObj->PartDesc = $partobj->PartDesc . "[نسخه جدید]";
	$newObj->PartDate = PDONOW;
	$newObj->PartAmount = $RemainAmount;
	$newObj->InstallmentCount = $remainInstallments;
	if(!$newObj->AddPart($pdo))
	{
		$pdo->rollBack();
		print_r($newObj);
		print_r(ExceptionHandler::PopAllExceptions());
		echo Response::createObjectiveResponse(false, "خطا در ایجاد فاز جدید");
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

function GetRequestTotalRemainder(){
	
	$remain = 0;
	$temp = PdoDataAccess::runquery("select PartID from LON_ReqParts where requestID=?", 
		array($_POST["RequestID"]));
	foreach($temp as $row)
	{
		$dt = LON_installments::SelectAll("PartID=?" , array($row["PartID"]));
		$returnArr = ComputePayments($row["PartID"], $dt);
		if(count($returnArr) > 0 && $returnArr[ count($returnArr) -1 ]["TotalRemainder"]*1 > 0)
			$remain += $returnArr[ count($returnArr) -1 ]["TotalRemainder"]*1;
	}
	
	echo Response::createObjectiveResponse(true, $remain);
	die();
}

function EndRequest(){
	
	$ReqObj = new LON_requests($_POST["RequestID"]);
	
	$pdo = PdoDataAccess::getPdoObject();
	$pdo->beginTransaction();
	
	if(!RegisterEndRequestDoc($ReqObj, $pdo))
	{
		$pdo->rollback();
		echo Response::createObjectiveResponse(false, "خطا در صدور سند");
		die();
	}
	
	$ReqObj->IsEnded = "YES";
	$ReqObj->StatusID = 101;
	if(!$ReqObj->EditRequest($pdo))
	{
		$pdo->rollback();
		echo Response::createObjectiveResponse(false, "خطا در تغییر درخواست");
		die();
	}
	
	$pdo->commit();
	echo Response::createObjectiveResponse(true, "");
	die();
}

function ReturnEndRequest(){
	
	$ReqObj = new LON_requests($_POST["RequestID"]);
	
	$pdo = PdoDataAccess::getPdoObject();
	$pdo->beginTransaction();
	
	if(!ReturnEndRequestDoc($ReqObj, $pdo))
	{
		$pdo->rollback();
		echo Response::createObjectiveResponse(false, "خطا در ابطال سند");
		die();
	}
	
	$ReqObj->IsEnded = "NO";
	$ReqObj->StatusID = 70;
	if(!$ReqObj->EditRequest($pdo))
	{
		$pdo->rollback();
		echo Response::createObjectiveResponse(false, "خطا در تغییر درخواست");
		die();
	}
	
	$pdo->commit();
	echo Response::createObjectiveResponse(true, "");
	die();
}
//------------------------------------------------

function GetPartInstallments(){
	
	$PartID = $_REQUEST["PartID"];
	
	$dt = LON_installments::SelectAll("PartID=? " . dataReader::makeOrder() , array($PartID));
	ComputePayments($PartID, $dt);
	
	$currentPay = 0;
	foreach($dt as $row)
		if($row["InstallmentDate"] < DateModules::Now() && $row["TotalRemainder"]*1 > 0)
			$currentPay += $row["TotalRemainder"]*1;
	
	echo dataReader::getJsonData($dt, count($dt), $_GET["callback"], $currentPay);
	die();
}

function ComputeInstallments(){
	
	$obj = new LON_ReqParts($_REQUEST["PartID"]);
	
	PdoDataAccess::runquery("delete from LON_installments where PartID=? ", array($obj->PartID));
	
	//-----------------------------------------------
	$YearMonths = 12;
	if($obj->IntervalType == "DAY")
		$YearMonths = floor(365/$obj->PayInterval);
	
	$TotalWage = round(ComputeWage($obj->PartAmount, $obj->CustomerWage/100, 
			$obj->InstallmentCount, $YearMonths, $obj->PayInterval));
	
	if($obj->WageReturn == "CUSTOMER")
	{
		$TotalWage = 0;
		$obj->CustomerWage = 0;
	}
	
	$allPay = roundUp( PMT($obj->CustomerWage, $obj->InstallmentCount, 
			$obj->PartAmount, $YearMonths, $obj->PayInterval), -3);
	$LastPay = $obj->PartAmount*1 + $TotalWage - $allPay*($obj->InstallmentCount-1);

	$jdate = DateModules::miladi_to_shamsi($obj->PartDate);
	$jdate = DateModules::AddToJDate($jdate, 1, $obj->DelayMonths);
	
	$pdo = PdoDataAccess::getPdoObject();
	$pdo->beginTransaction();
	
	for($i=0; $i < $obj->InstallmentCount-1; $i++)
	{
		$obj2 = new LON_installments();
		
		$obj2->InstallmentDate = DateModules::AddToJDate($jdate, 
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
		
	$obj2->InstallmentDate = DateModules::AddToJDate($jdate, 
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

function selectParts(){
	
	$params = array();
	$query = "select p.*,r.IsEnded, concat_ws(' ',fname,lname,CompanyName) loanFullname
		from LON_ReqParts p
		join LON_requests r using(RequestID)
		join BSC_persons on(LoanPersonID=PersonID)
		where 1=1";
	if(!empty($_REQUEST["query"]))
	{
		$query .= " AND concat_ws(' ',fname,lname,CompanyName) like :f";
		$params[":f"] = "%" . $_REQUEST["query"] . "%";
	}
	
	if(isset($_SESSION["USER"]["portal"]))
		$query .= " AND LoanPersonID=" . $_SESSION["USER"]["PersonID"];
	
	$dt = PdoDataAccess::runquery_fetchMode($query, $params);
	$cnt = $dt->rowCount();
	
	if(!empty($_REQUEST["limit"]))
		$dt = PdoDataAccess::fetchAll($dt, $_REQUEST["start"], $_REQUEST["limit"]);
	else
		$dt = $dt->fetchAll();
	
	echo dataReader::getJsonData($dt, $cnt, $_GET["callback"]);
	die();
}

function SelectReadyToPayParts(){
	
	$dt = PdoDataAccess::runquery("select max(StepID) from WFM_FlowSteps where FlowID=1");

	$dt = PdoDataAccess::runquery("
		select RequestID,PartAmount,PartDesc,PartDate,
			if(p1.IsReal='YES',concat(p1.fname, ' ', p1.lname),p1.CompanyName) ReqFullname,
			if(p2.IsReal='YES',concat(p2.fname, ' ', p2.lname),p2.CompanyName) LoanFullname
			
		from WFM_FlowRows fr
		join WFM_FlowSteps using(StepRowID)
		join LON_ReqParts on(PartID=ObjectID)
		join LON_requests r using(RequestID)
		left join BSC_persons p1 on(p1.PersonID=r.ReqPersonID)
		left join BSC_persons p2 on(p2.PersonID=r.LoanPersonID)
		left join ACC_DocItems di on(SourceType=" . DOCTYPE_LOAN_PAYMENT . " AND SourceID=RequestID AND SourceID2=PartID)
		where fr.FlowID=1 AND StepID=? AND ActionType='CONFIRM' AND di.ItemID is null",
		array($dt[0][0]));

	//print_r(ExceptionHandler::PopAllExceptions());
	echo dataReader::getJsonData($dt, count($dt), $_GET["callback"]);
	die();
}

function SelectReceivedRequests(){
	
	$where = "StatusID in (10,50)";
	 
	$branches = FRW_access::GetAccessBranches();
	$where .= " AND BranchID in(" . implode(",", $branches) . ")";
	
	$dt = LON_requests::SelectAll($where);
	echo dataReader::getJsonData($dt->fetchAll(), $dt->rowCount(), $_GET["callback"]);
	die();
}

function selectRequestStatuses(){
	$dt = PdoDataAccess::runquery("select * from BaseInfo where typeID=5");
	echo dataReader::getJsonData($dt, count($dt), $_GET["callback"]);
	die();
}

//------------------------------------------------

function GetPartPays(){
	
	$dt = LON_pays::SelectAll("PartID=? " . dataReader::makeOrder() , array($_REQUEST["PartID"]));
	echo dataReader::getJsonData($dt, count($dt), $_GET["callback"]);
	die();
}

function SavePartPay(){
	
	$obj = new LON_pays();
	PdoDataAccess::FillObjectByJsonData($obj, $_POST["record"]);
	
	$pdo = PdoDataAccess::getPdoObject();
	$pdo->beginTransaction();
	
	if(empty($obj->PayID))
		$result = $obj->AddPay($pdo);
	else
		$result = $obj->EditPay($pdo);
	
	if(!$result)
	{
		$pdo->rollback();
		echo Response::createObjectiveResponse(false, "خطا در ثبت ردیف پرداخت");
		die();
	}
	if(empty($obj->ChequeNo) || $obj->ChequeStatus == "2")
	{
		if(!RegisterCustomerPayDoc($obj, $pdo))
		{
			$pdo->rollback();
			//print_r(ExceptionHandler::PopAllExceptions());
			echo Response::createObjectiveResponse(false, "خطا در صدور سند حسابداری");
			die();
		}
	}
	$pdo->commit();
	echo Response::createObjectiveResponse(true, "");
	die();
}

function DeletePay(){
	
	$pdo = PdoDataAccess::getPdoObject();
	$pdo->beginTransaction();
	
	$PayObj = new LON_pays($_POST["PayID"]);
	if(!ReturnCustomerPayDoc($PayObj, $pdo))
	{
		$pdo->rollBack();
		//print_r(ExceptionHandler::PopAllExceptions());
		echo Response::createObjectiveResponse(false, ExceptionHandler::GetExceptionsToString());
		die();
	}
	
	if(!LON_pays::DeletePay($_POST["PayID"], $pdo))
	{
		$pdo->rollBack();
		echo Response::createObjectiveResponse(false, "خطا در حذف ردیف پرداخت");
		die();
	}
	$pdo->commit();
	echo Response::createObjectiveResponse(true, "");
	die();
}

function ComputePayments($PartID, &$installments){
	
	$obj = new LON_ReqParts($PartID);
	if($obj->PayCompute == "installment")
		return ComputePaymentsBaseOnInstallment ($PartID, $installments);
	
	$returnArr = array();
	$pays = PdoDataAccess::runquery("
		select p.PayDate, sum(PayAmount) PayAmount
			from LON_pays p
			left join BaseInfo bi on(bi.TypeID=6 AND bi.InfoID=p.PayType)
			join LON_ReqParts rp using(PartID)
			left join ACC_banks b on(ChequeBank=BankID)
			where PartID=? AND if(p.ChequeNo<>'',p.ChequeStatus=2,1=1)
			group by PayDate 
			order by PayDate" , array($PartID));
	$PayRecord = count($pays) == 0 ? null : $pays[0];
	$payIndex = 1;
	$Forfeit = 0;
	for($i=0; $i < count($installments); $i++)
	{
		$installments[$i]["ForfeitAmount"] = 0;
		$installments[$i]["ForfeitDays"] = 0;
		$installments[$i]["remainder"] = 0;
		$installments[$i]["PayAmount"] = 0;
		$installments[$i]["PayDate"] = '';
		$installments[$i]["TotalRemainder"] = 0;
		if($PayRecord == null)
		{
			$ToDate = DateModules::Now();
			$amount = $installments[$i]["InstallmentAmount"];
			if ($installments[$i]["InstallmentDate"] < $ToDate) {
				$forfeitDays = DateModules::GDateMinusGDate($ToDate,$installments[$i]["InstallmentDate"]);
				$installments[$i]["ForfeitDays"] = $forfeitDays;
				$installments[$i]["ForfeitAmount"] = round($amount*$installments[$i]["ForfeitPercent"]*$forfeitDays/36500);
				$installments[$i]["TotalRemainder"] = $amount + $installments[$i]["ForfeitAmount"] ;
				$installments[$i]["remainder"] = $amount;
			}
			else
			{
				$installments[$i]["ForfeitDays"] = 0;
				$installments[$i]["ForfeitAmount"] = 0;
				$installments[$i]["TotalRemainder"] = $amount;
			}
			$returnArr[] = $installments[$i];
			continue;
		}
		
		$remainder = $installments[$i]["InstallmentAmount"];
		$StartDate = $installments[$i]["InstallmentDate"];

		while(true)
		{
			if($remainder == 0)
				break;	
			$ToDate = $PayRecord == null ? DateModules::Now() : $PayRecord["PayDate"];
			if($PayRecord != null)
			{
				$installments[$i]["PayAmount"] = $PayRecord["PayAmount"]*1;
				$installments[$i]["PayDate"] = $PayRecord["PayDate"];
			}
			else
			{
				$installments[$i]["PayAmount"] = 0;
				$installments[$i]["PayDate"] = DateModules::Now();
			}
			if ($StartDate < $ToDate) {
				
				$forfeitDays = DateModules::GDateMinusGDate($ToDate,$StartDate);
				$Forfeit += round($remainder*$installments[$i]["ForfeitPercent"]*$forfeitDays/36500);
				$installments[$i]["ForfeitDays"] = $forfeitDays;
				$installments[$i]["ForfeitAmount"] = $Forfeit;
			}		
			
			if($PayRecord == null)
			{
				$installments[$i]["TotalRemainder"] += $Forfeit;
				$installments[$i]["remainder"] = $remainder;
				$returnArr[] = $installments[$i];
				break;
			}
			//----------------------------------------------
			if($PayRecord["PayAmount"]*1 <= $Forfeit)
			{
				$Forfeit = $Forfeit - $PayRecord["PayAmount"]*1;
				$installments[$i]["TotalRemainder"] = $remainder + $Forfeit;
				$installments[$i]["remainder"] = $remainder;
				$StartDate = max($PayRecord["PayDate"],$installments[$i]["InstallmentDate"]);
				$PayRecord = $payIndex < count($pays) ? $pays[$payIndex++] : null;
				$returnArr[] = $installments[$i];
				continue;
			}
			
			$PayRecord["PayAmount"] = $PayRecord["PayAmount"]*1 - $Forfeit;
			$Forfeit = 0;			
			
			if($remainder < $PayRecord["PayAmount"]*1)
			{
				$PayRecord["PayAmount"] = $PayRecord["PayAmount"]*1 - $remainder;
				if($PayRecord["PayAmount"] == 0)
				{
					$StartDate = max($PayRecord["PayDate"],$installments[$i]["InstallmentDate"]);
					$PayRecord = $payIndex < count($pays) ? $pays[$payIndex++] : null;
				}
				if($i == count($installments)-1)	
				{
					$installments[$i]["TotalRemainder"] = -1*$PayRecord["PayAmount"];
					$installments[$i]["remainder"] = -1*$PayRecord["PayAmount"];
				}
				else
				{
					$installments[$i]["TotalRemainder"] = 0;
					$installments[$i]["remainder"] = 0;
				}
				
				$returnArr[] = $installments[$i];
				break;
			}
						
			$remainder = $remainder - $PayRecord["PayAmount"]*1;
			$StartDate = max($PayRecord["PayDate"],$installments[$i]["InstallmentDate"]);
			
			$installments[$i]["TotalRemainder"] = $remainder;
			$installments[$i]["remainder"] = $remainder;
			
			$PayRecord = $payIndex < count($pays) ? $pays[$payIndex++] : null;
			$returnArr[] = $installments[$i];
		}
	}
	
	return $returnArr;
}

function ComputePaymentsBaseOnInstallment($PartID, &$installments){
	
	$returnArr = array();
	$pays = PdoDataAccess::runquery("
		select p.PayDate, sum(PayAmount) PayAmount
			from LON_pays p
			left join BaseInfo bi on(bi.TypeID=6 AND bi.InfoID=p.PayType)
			join LON_ReqParts rp using(PartID)
			left join ACC_banks b on(ChequeBank=BankID)
			where PartID=? AND if(p.ChequeNo<>'',p.ChequeStatus=2,1=1)
			group by PayDate" , array($PartID));
	$PayRecord = count($pays) == 0 ? null : $pays[0];
	$payIndex = 1;
	$Forfeit = 0;
	for($i=0; $i < count($installments); $i++)
	{
		$installments[$i]["ForfeitAmount"] = 0;
		$installments[$i]["ForfeitDays"] = 0;
		$installments[$i]["remainder"] = 0;
		$installments[$i]["PayAmount"] = 0;
		$installments[$i]["PayDate"] = '';
		$installments[$i]["TotalRemainder"] = 0;
		
		if($PayRecord == null)
		{
			$ToDate = DateModules::Now();
			$amount = $installments[$i]["InstallmentAmount"];
			if ($installments[$i]["InstallmentDate"] < $ToDate) {
				$forfeitDays = DateModules::GDateMinusGDate($ToDate,$installments[$i]["InstallmentDate"]);
				$installments[$i]["ForfeitDays"] = $forfeitDays;
				$installments[$i]["ForfeitAmount"] = round($amount*$installments[$i]["ForfeitPercent"]*$forfeitDays/36500);
				$installments[$i]["remainder"] = $amount;
				$installments[$i]["TotalRemainder"] = $amount + $installments[$i]["ForfeitAmount"] ;
			}
			else
			{
				$installments[$i]["ForfeitDays"] = 0;
				$installments[$i]["ForfeitAmount"] = 0;
				$installments[$i]["TotalRemainder"] = $amount;
			}
			$returnArr[] = $installments[$i];
			continue;
		}
		
		$remainder = $installments[$i]["InstallmentAmount"];
		$StartDate = $installments[$i]["InstallmentDate"];

		while(true)
		{
			$ToDate = $PayRecord == null ? DateModules::Now() : $PayRecord["PayDate"];
			if($PayRecord != null)
			{
				$installments[$i]["PayAmount"] = $PayRecord["PayAmount"]*1;
				$installments[$i]["PayDate"] = $PayRecord["PayDate"];
			}
			else
			{
				$installments[$i]["PayAmount"] = 0;
				$installments[$i]["PayDate"] = DateModules::Now();
			}
			if ($StartDate < $ToDate) {
				
				$forfeitDays = DateModules::GDateMinusGDate($ToDate,$StartDate);
				$Forfeit += round($remainder*$installments[$i]["ForfeitPercent"]*$forfeitDays/36500);
				$installments[$i]["ForfeitDays"] = $forfeitDays;
				$installments[$i]["ForfeitAmount"] = $Forfeit;
			}		
			
			if($PayRecord == null)
			{
				$installments[$i]["TotalRemainder"] += round($remainder*$installments[$i]["ForfeitPercent"]*$forfeitDays/36500);
				$installments[$i]["remainder"] = $remainder;
				$returnArr[] = $installments[$i];
				break;
			}
			
			if($remainder <= $PayRecord["PayAmount"]*1)
			{
				$PayRecord["PayAmount"] = $PayRecord["PayAmount"]*1 - $remainder;
				$remainder = 0;
								
				$installments[$i]["TotalRemainder"] = $Forfeit;
				$installments[$i]["remainder"] = 0;
				
				if($PayRecord["PayAmount"] == 0)
				{
					$StartDate = $PayRecord["PayDate"];
					$PayRecord = $payIndex < count($pays) ? $pays[$payIndex++] : null;
				}	
				$returnArr[] = $installments[$i];
				
				break;
			}
						
			$remainder = $remainder - $PayRecord["PayAmount"]*1;
			$StartDate = $PayRecord["PayDate"];
			
			$installments[$i]["TotalRemainder"] = $remainder + $Forfeit;
			$installments[$i]["remainder"] = $remainder;
			
			$PayRecord = $payIndex < count($pays) ? $pays[$payIndex++] : null;
			$returnArr[] = $installments[$i];
		}
	}
	
	if($Forfeit > 0)
	{
		while(true)
		{
			if($PayRecord["PayAmount"] > 0)
			{
				$installments[$i]["InstallmentDate"] = "---";
				$installments[$i]["InstallmentAmount"] = 0;
				$installments[$i]["PayAmount"] = $PayRecord["PayAmount"];
				$installments[$i]["PayDate"] = $PayRecord["PayDate"];
				$Forfeit = $Forfeit - $PayRecord["PayAmount"]*1;
				$installments[$i]["ForfeitDays"] = 0;	
				$installments[$i]["TotalRemainder"] = $Forfeit;
				$installments[$i]["ForfeitAmount"] = $Forfeit;
				$installments[$i]["remainder"] = 0;
				$returnArr[] = $installments[$i];
			}
			$PayRecord = $payIndex < count($pays) ? $pays[$payIndex++] : null;
			if($PayRecord == null)
				break;
		}
	}
	
	return $returnArr;
}

//------------------------------------------------

function GetDelayedInstallments(){
	
	$query = "select PartID,concat_ws(' ',fname,lname,CompanyName) LoanPersonName
			from LON_installments p
			join LON_ReqParts rp using(PartID)
			join LON_requests using(RequestID)
			join BSC_persons on(LoanPersonID=PersonID)
			
			where InstallmentDate<now() AND IsEnded='NO'
			group by PartID";
	$dt = PdoDataAccess::runquery_fetchMode($query);
	
	$result = array();
	while($row = $dt->fetch())
	{
		$temp = LON_installments::SelectAll("PartID=?" , array($row["PartID"]));
		$returnArr = ComputePayments($row["PartID"], $temp);
		
		foreach($returnArr as $row2)
			if(isset($row2["InstallmentID"]) && $row2["InstallmentID"]*1 > 0)
			{
				if(count($result) > 0 && $result[ count($result)-1 ]["InstallmentID"] == $row2["InstallmentID"])
				{
					if($row2["remainder"]*1 == 0)
						array_pop ($result);
					else
						$result[ count($result)-1 ]["remainder"] = $row2["remainder"];
				}
				else if($row2["remainder"]*1 > 0)
				{
					$row2["LoanPersonName"] = $row["LoanPersonName"];
					$result[] = $row2;
				}
			}
	}
	
	//echo PdoDataAccess::GetLatestQueryString();
	
	$cnt = count($result);
	$result = array_slice($result, $_REQUEST["start"], $_REQUEST["limit"]);
	
	echo dataReader::getJsonData($result, $cnt, $_GET["callback"]);
	die();
}

function GetEndedRequests(){
	
	$query = "select rp.RequestID,ReqDate,PartID,concat_ws(' ',fname,lname,CompanyName) LoanPersonName
			from LON_ReqParts rp
			join LON_requests using(RequestID)
			join BSC_persons on(LoanPersonID=PersonID)
						
			where IsEnded='NO' 
			group by rp.PartID
			order by rp.RequestID";
	$dt = PdoDataAccess::runquery_fetchMode($query);
	
	$result = array();
	while($row = $dt->fetch())
	{
		$temp = LON_installments::SelectAll("PartID=?" , array($row["PartID"]));
		$returnArr = ComputePayments($row["PartID"], $temp);
		$row["TotalRemainder"] = count($returnArr) > 0 ? $returnArr[ count($returnArr)-1 ]["TotalRemainder"] : 0;
		
		if(count($returnArr) == 0)
			continue;
		
		if($returnArr[ count($returnArr)-1 ]["TotalRemainder"]*1 == 0)
		{
			if(count($result) > 0 && $result[ count($result)-1 ]["RequestID"] == $row["RequestID"])
				continue;
			$result[] = $row;
		}
		else
		{
			if(count($result) > 0 && $result[ count($result)-1 ]["RequestID"] == $row["RequestID"])
			{
				array_pop($result);
				while($row["RequestID"] == $result[ count($result)-1 ]["RequestID"])
					$row = $dt->fetch();
			}
		}
	}
	
	$cnt = count($result);
	$result = array_slice($result, $_REQUEST["start"], $_REQUEST["limit"]);
	
	echo dataReader::getJsonData($result, $cnt, $_GET["callback"]);
	die();
}
?>
