<?php
//-------------------------
// programmer:	Jafarkhani
// create Date: 94.06
//-------------------------

require_once('../header.inc.php');
include_once inc_dataReader;
include_once inc_response;
include_once 'request.class.php';
require_once getenv("DOCUMENT_ROOT") . '/loan/loan/loan.class.php';
require_once "../../office/workflow/wfm.class.php";
require_once '../../accounting/docs/import.data.php';
require_once '../../framework/person/persons.class.php';
require_once 'compute.inc.php';

$task = isset($_REQUEST["task"]) ? $_REQUEST["task"] : "";
if(!empty($task))
{
	$task();
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
	$obj->IsFree = isset($_POST["IsFree"]) ? "YES" : "NO";	
	//------------------------------------------------------
	if(isset($_SESSION["USER"]["portal"]))
	{
		if($_SESSION["USER"]["IsAgent"] == "YES" || $_SESSION["USER"]["IsSupporter"] == "YES")
		{
			$obj->ReqPersonID = $_SESSION["USER"]["PersonID"];
			
			if(isset($_POST["sending"]) &&  $_POST["sending"] == "true")
				$obj->StatusID = 10;
			else
				$obj->StatusID = 1;

			$obj->LoanID = Default_Agent_Loan;
		}
		if($_SESSION["USER"]["IsCustomer"] == "YES")
		{
			if(!isset($obj->LoanPersonID))
				$obj->LoanPersonID = $_SESSION["USER"]["PersonID"];
			$obj->StatusID = 10;
		}
	}
	else if(empty($obj->RequestID))
	{
		$obj->LoanID = Default_Agent_Loan;
		$obj->StatusID = 1;
	}
	if(empty($obj->RequestID))
	{
		$obj->AgentGuarantee = isset($_POST["AgentGuarantee"]) ? "YES" : "NO";
		$result = $obj->AddRequest();
		if($result)
			ChangeStatus($obj->RequestID,$obj->StatusID, "", true);
		else
		{
			echo Response::createObjectiveResponse(false, ExceptionHandler::GetExceptionsToString());
			die();
		}
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
		else
		{
			echo Response::createObjectiveResponse(false, ExceptionHandler::GetExceptionsToString());
			die();
		}
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

function SelectAllRequests2(){
	
	$params = array();
	$query = "select p.*,r.IsEnded, concat_ws(' ',fname,lname,CompanyName) loanFullname,
				i.InstallmentAmount
		from LON_requests r 
		join LON_ReqParts p on(r.RequestID=p.RequestID AND IsHistory='NO')
		join BSC_persons on(LoanPersonID=PersonID)
		join LON_installments i on(i.RequestID=p.RequestID)		
		where 1=1";
	if(!empty($_REQUEST["query"]))
	{
		$query .= " AND ( concat_ws(' ',fname,lname,CompanyName) like :f or r.RequestID = :f1)";
		$params[":f"] = "%" . $_REQUEST["query"] . "%";
		$params[":f1"] = $_REQUEST["query"] ;
	}
	
	if(isset($_SESSION["USER"]["portal"]))
		$query .= " AND LoanPersonID=" . $_SESSION["USER"]["PersonID"];
	
	$query .= " group by r.RequestID";
	
	$dt = PdoDataAccess::runquery_fetchMode($query, $params);
	$cnt = $dt->rowCount();
	
	if(!empty($_REQUEST["limit"]))
		$dt = PdoDataAccess::fetchAll($dt, $_REQUEST["start"], $_REQUEST["limit"]);
	else
		$dt = $dt->fetchAll();
	
	echo dataReader::getJsonData($dt, $cnt, $_GET["callback"]);
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
	if(!empty($_REQUEST['query']) && empty($_REQUEST["fields"]))
	{
		$where .= " AND ( concat_ws(' ',fname,lname,CompanyName) like :f or RequestID = :f1)";
		$params[":f"] = "%" . $_REQUEST["query"] . "%";
		$params[":f1"] = $_REQUEST["query"] ;
	}
	if(!empty($_REQUEST["IsEnded"]))
	{
		$where .= " AND IsEnded = :e "; 
		$param[":e"] = $_REQUEST["IsEnded"];
	}
	if(!empty($_REQUEST["IsConfirm"]))
	{
		$where .= " AND r.IsConfirm = :e "; 
		$param[":e"] = $_REQUEST["IsConfirm"];
	}
	
	$where .= dataReader::makeOrder();
	$dt = LON_requests::SelectAll($where, $param);
	print_r(ExceptionHandler::PopAllExceptions());
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
	//print_r(ExceptionHandler::PopAllExceptions());
	echo Response::createObjectiveResponse($res, !$res ? ExceptionHandler::GetExceptionsToString() : "");
	die();
}

function ChangeStatus($RequestID, $StatusID, $StepComment = "", $LogOnly = false, $pdo = null, $UpdateOnly = false){
	
	if(!$LogOnly)
	{
		$obj = new LON_requests();
		$obj->RequestID = $RequestID;
		$obj->StatusID = $StatusID;
		if(!$obj->EditRequest($pdo))
			return false;
	}
	if(!$UpdateOnly)
	{
		PdoDataAccess::runquery("insert into LON_ReqFlow(RequestID,PersonID,StatusID,ActDate,StepComment) 
		values(?,?,?,now(),?)", array(
			$RequestID,
			$_SESSION["USER"]["PersonID"],
			$StatusID,
			$StepComment
		), $pdo);
	}
	return ExceptionHandler::GetExceptionCount() == 0;
}

function ChangeRequestStatus(){
	
	if($_POST["StatusID"] == "11")
	{
		$result = ChangeStatus($_POST["RequestID"],$_POST["StatusID"],$_POST["StepComment"], true);
		$result = ChangeStatus($_POST["RequestID"],1,$_POST["StepComment"], false, null, true);
		Response::createObjectiveResponse($result, "");
		die();
	}
	
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
		
		$temp = PdoDataAccess::runquery("select count(*)
			from ACC_DocItems join ACC_docs using(DocID) where 
			 CostID=? AND SourceType=" . DOCTYPE_LOAN_PAYMENT . "  
				 AND DocStatus in('CONFIRM','ARCHIVE') AND SourceID=? AND SourceID2=? ", 
			array($CostCode_commitment, $dt[$i]["RequestID"], $dt[$i]["PartID"]));
		$dt[$i]["IsDocRegister"] = $temp[0][0]*1 > 0 ? "YES" : "NO"; 	
		
		
		$dt[$i]["IsStarted"] = WFM_FlowRows::IsFlowStarted(1, $dt[$i]["PartID"]) ? "YES" : "NO";
		$dt[$i]["IsEnded"] = WFM_FlowRows::IsFlowEnded(1, $dt[$i]["PartID"]) ? "YES" : "NO";
		
	}
	
	echo dataReader::getJsonData($dt, count($dt), $_GET["callback"]);
	die();	
}

function SavePart(){
	
	$obj = new LON_ReqParts();
	PdoDataAccess::FillObjectByArray($obj, $_POST);
	
	$pdo = PdoDataAccess::getPdoObject();
	$pdo->beginTransaction();
	
	$dt = LON_ReqParts::SelectAll("RequestID=?", array($obj->RequestID));
	$firstPart = count($dt) > 0 ? false : true;
		
	if($obj->PartID > 0)
	{
		$result = $obj->EditPart($pdo);
		if(!$firstPart)
		{
			$dt = PdoDataAccess::runquery("select max(DocID) from ACC_DocItems join ACC_docs using(DocID)
				where DocType=" . DOCTYPE_LOAN_DIFFERENCE ." AND SourceID=? AND SourceID2=?",
				array($obj->RequestID, $obj->PartID));
			if($dt[0][0] != "")
			{
				$result = RegisterDifferncePartsDoc($obj->RequestID,$obj->PartID, $pdo, $dt[0][0]);
				ComputeInstallments($obj->RequestID, true, $pdo);
			}
		}		
	}
	else
	{
		$result = $obj->AddPart($pdo);
		
		if(!$firstPart)
		{
			foreach($dt as $row)
			{
				$partobj = new LON_ReqParts($row["PartID"]);
				if($partobj->IsHistory == "NO")
				{
					$partobj->IsHistory = "YES";
					$partobj->EditPart($pdo);
					ChangeStatus($partobj->RequestID, "100", $partobj->PartDesc, true, $pdo);
				}
			}
			$result = RegisterDifferncePartsDoc($obj->RequestID,$obj->PartID, $pdo);
			ComputeInstallments($obj->RequestID, true, $pdo);
		}		
	}

	if(!$result)
	{
		print_r(ExceptionHandler::PopAllExceptions());
		echo Response::createObjectiveResponse(false, ExceptionHandler::GetExceptionsToString());
		die();
	}
	$pdo->commit();
	echo Response::createObjectiveResponse(true, "");
	die();
}

function DeletePart(){
	
	$obj = new LON_ReqParts($_POST["PartID"]);
	
	$dt = PdoDataAccess::runquery("select * from ACC_DocItems join ACC_docs using(DocID)
		where SourceType=" . DOCTYPE_LOAN_DIFFERENCE . "
		AND SourceID=? AND SourceID2=?", array($obj->RequestID, $obj->PartID));
	
	if(count($dt) > 0 && $dt[0]["DocStatus"] != "RAW")
	{
		echo Response::createObjectiveResponse(false, "سند اختلاف تایید شده و قادر به حذف نمی باشید");
		die();
	}
	
	$pdo = PdoDataAccess::getPdoObject();
	$pdo->beginTransaction();
	
	if(count($dt) > 0)
	{
		if(!ACC_docs::Remove($dt[0]["DocID"], $pdo))
		{
			echo Response::createObjectiveResponse(false, "خطا در حذف سند");
			die();
		}
	}
	if(!LON_ReqParts::DeletePart($_POST["PartID"], $pdo))
	{
		echo Response::createObjectiveResponse(false, "خطا در حذف فاز");
		die();
	}
	
	$dt = PdoDataAccess::runquery("select PartID from LON_ReqParts where RequestID=? order by PartID desc", 
			array($obj->RequestID), $pdo);
	if(count($dt)> 0)
	{
		$obj2 = new LON_ReqParts($dt[0]["PartID"]);
		$obj2->IsHistory = "NO";
		$obj2->EditPart($pdo);
	}
	ComputeInstallments($obj->RequestID, true, $pdo);
	
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
	$RequestID = $_REQUEST["RequestID"];
	$dt = LON_installments::SelectAll("r.RequestID=?" , array($RequestID));
	$returnArr = ComputePayments($RequestID, $dt);
	if(count($returnArr) > 0 && $returnArr[ count($returnArr) -1 ]["TotalRemainder"]*1 > 0)
		$remain = $returnArr[ count($returnArr) -1 ]["TotalRemainder"]*1;
	
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

//--------------------------------------------------

function GetInstallments(){
	
	$RequestID = $_REQUEST["RequestID"];
	
	$temp = LON_installments::SelectAll("r.RequestID=? " . dataReader::makeOrder() , array($RequestID));
	$dt = ComputePayments($RequestID, $temp);
	$currentPay = 0;
	foreach($dt as $row)
	{	
		if($row["InstallmentDate"] < DateModules::Now() && $row["TotalRemainder"]*1 > 0)
			$currentPay += $row["TotalRemainder"]*1;
	}
	echo dataReader::getJsonData($temp, count($temp), $_GET["callback"], $currentPay);
	die();
}

function ComputeInstallments($RequestID = "", $returnMode = false, $pdo2 = null){
	
	$RequestID = empty($RequestID) ? $_REQUEST["RequestID"] : $RequestID;
	
	PdoDataAccess::runquery("delete from LON_installments where RequestID=? ", array($RequestID));
	//-----------------------------------------------
	$obj2 = new LON_requests($RequestID);
	if($obj2->ReqPersonID == SHEKOOFAI)
		return ComputeInstallmentsShekoofa($RequestID, $returnMode);
	//-----------------------------------------------
	$obj = LON_ReqParts::GetValidPartObj($RequestID);
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
	$startDate = DateModules::miladi_to_shamsi($obj->PartDate);
	$DelayDuration = DateModules::JDateMinusJDate(
		DateModules::AddToJDate($startDate, $obj->DelayDays, $obj->DelayMonths), $startDate)+1;
	
	if($obj->DelayDays*1 > 0)
		$TotalDelay = round($obj->PartAmount*$obj->DelayPercent*$DelayDuration/36500);
	else
		$TotalDelay = round($obj->PartAmount*$obj->DelayPercent*$obj->DelayMonths/1200);
	
	//-------------------------- installments -----------------------------
	$MaxWage = max($obj->CustomerWage, $obj->FundWage);
	$CustomerFactor =	$MaxWage == 0 ? 0 : $obj->CustomerWage/$MaxWage;
	$FundFactor =		$MaxWage == 0 ? 0 : $obj->FundWage/$MaxWage;
	$AgentFactor =		$MaxWage == 0 ? 0 : ($obj->CustomerWage-$obj->FundWage)/$MaxWage;
	
	$extraAmount = 0;
	if($obj->WageReturn == "INSTALLMENT")
	{
		if($obj->MaxFundWage*1 > 0)
			$extraAmount += $obj->MaxFundWage;
		else if($obj->CustomerWage > $obj->FundWage)
			$extraAmount += round($TotalWage*$FundFactor);
		else
			$extraAmount += round($TotalWage*$CustomerFactor);		
	}		
	if($obj->AgentReturn == "INSTALLMENT" && $obj->CustomerWage>$obj->FundWage)
		$extraAmount += round($TotalWage*$AgentFactor);

	if($obj->DelayReturn == "INSTALLMENT")
		$extraAmount += $TotalDelay*($obj->FundWage/$obj->DelayPercent);
	if($obj->AgentDelayReturn == "INSTALLMENT" && $obj->DelayPercent>$obj->FundWage)
		$extraAmount += $TotalDelay*(($obj->DelayPercent-$obj->FundWage)/$obj->DelayPercent);

	$TotalAmount = $obj->PartAmount*1 + $extraAmount;
	
	$allPay = ComputeInstallmentAmount($TotalAmount,$obj->InstallmentCount, $obj->PayInterval);
	
	if($obj->InstallmentCount > 1)
		$allPay = roundUp($allPay,-3);
	else
		$allPay = round($allPay);
	
	if($obj->DelayReturn == "INSTALLMENT")
		$allPay += $TotalDelay/$obj->InstallmentCount*1;
	
	$LastPay = $TotalAmount - $allPay*($obj->InstallmentCount-1);
	
	//---------------------------------------------------------------------
	
	$jdate = DateModules::miladi_to_shamsi($obj->PartDate);
	$jdate = DateModules::AddToJDate($jdate, $obj->DelayDays, $obj->DelayMonths);
	
	if($pdo2 == null)
	{
		$pdo = PdoDataAccess::getPdoObject();
		$pdo->beginTransaction();
	}
	else
		$pdo = $pdo2;
	for($i=0; $i < $obj->InstallmentCount-1; $i++)
	{
		$obj2 = new LON_installments();
		$obj2->RequestID = $RequestID;
		$obj2->InstallmentDate = DateModules::AddToJDate($jdate, 
			$obj->IntervalType == "DAY" ? $obj->PayInterval*($i+1) : 0, 
			$obj->IntervalType == "MONTH" ? $obj->PayInterval*($i+1) : 0);
		$obj2->InstallmentAmount = $allPay;
		if(!$obj2->AddInstallment($pdo))
		{
			$pdo->rollBack();
			print_r(ExceptionHandler::PopAllExceptions());
			echo Response::createObjectiveResponse(false, "");
			die();
		}
	}
	
	$obj2 = new LON_installments();
	$obj2->RequestID = $RequestID;
	$obj2->InstallmentDate = DateModules::AddToJDate($jdate, 
	$obj->IntervalType == "DAY" ? $obj->PayInterval*($obj->InstallmentCount) : 0, 
	$obj->IntervalType == "MONTH" ? $obj->PayInterval*($obj->InstallmentCount) : 0);
	$obj2->InstallmentAmount = $LastPay;
	if(!$obj2->AddInstallment($pdo))
	{
		$pdo->rollBack();
		print_r(ExceptionHandler::PopAllExceptions());
		echo Response::createObjectiveResponse(false, "");
		die();
	}
	
	if($returnMode)
		return true;
	
	$pdo->commit();	
	echo Response::createObjectiveResponse(true, "");
	die();
}

function ComputeInstallmentsShekoofa($RequestID = "", $returnMode = false){
	
	$RequestID = empty($RequestID) ? $_REQUEST["RequestID"] : $RequestID;
	$partObj = LON_ReqParts::GetValidPartObj($RequestID);
	
	$payments = LON_payments::Get(" AND RequestID=? order by PayDate", array($RequestID));
	$payments = $payments->fetchAll();
	
	if(count($payments) == 0)
		return true;
	//--------------- total pay months -------------
	$firstPay = DateModules::miladi_to_shamsi($payments[0]["PayDate"]);
	$LastPay = DateModules::miladi_to_shamsi($payments[count($payments)-1]["PayDate"]);
	$paymentPeriod = DateModules::GetDiffInMonth($firstPay, $LastPay);
	//----------------------------------------------	
	$totalWage = ComputeWageOfSHekoofa($partObj);
	
	$pdo = PdoDataAccess::getPdoObject();
	$pdo->beginTransaction();
	
	for($i=0; $i < $partObj->InstallmentCount; $i++)
	{
		$monthplus = $paymentPeriod + $partObj->DelayMonths*1 + ($i+1)*$partObj->PayInterval*1;
			
		$installmentDate = DateModules::miladi_to_shamsi($payments[0]["PayDate"]);
		$installmentDate = DateModules::AddToJDate($installmentDate, 0, $monthplus);
		$installmentDate = DateModules::shamsi_to_miladi($installmentDate);
			
		$obj2 = new LON_installments();
		$obj2->RequestID = $RequestID;
		$obj2->InstallmentDate = $installmentDate;
		$obj2->InstallmentAmount = round($partObj->PartAmount/$partObj->InstallmentCount) + 
				round($totalWage/$partObj->InstallmentCount);
		
		if(!$obj2->AddInstallment($pdo))
		{
			$pdo->rollBack();
			echo Response::createObjectiveResponse(false, "");
			die();
		}
	}
	
	$pdo->commit();
	
	if($returnMode)
		return true;
	
	echo Response::createObjectiveResponse(true, "");
	die();
}

function SaveInstallment(){
	
	$obj = new LON_installments();
	PdoDataAccess::FillObjectByJsonData($obj, $_POST["record"]);
	
	$result = $obj->EditInstallment();
	echo Response::createObjectiveResponse($result, "");
	die();
}

function DelayInstallments(){
	
	$RequestID = $_POST["RequestID"];
	$InstallmentID = $_POST["InstallmentID"];
	$newDate = $_POST["newDate"];
	
	$PartObj = LON_ReqParts::GetValidPartObj($RequestID);
	
	$pdo = PdoDataAccess::getPdoObject();
	$pdo->beginTransaction();
	
	if($_POST["IsRemainCompute"] == " 0")
	{
		$dt = LON_installments::SelectAll("r.RequestID=? AND InstallmentID>=?", array($RequestID, $InstallmentID));
		$days = 0;
		for($i=0; $i<count($dt); $i++)
		{
			$obj = new LON_installments();
			$obj->InstallmentID = $dt[$i]["InstallmentID"];
			$obj->IsDelayed = "YES";
			if(!$obj->EditInstallment($pdo))
			{
				$pdo->rollBack ();
				echo Response::createObjectiveResponse(false, "1");
				die();
			}
			//...........................................

			if($days == 0)
			{
				$newDate = DateModules::shamsi_to_miladi($newDate, "-");
				$days = DateModules::GDateMinusGDate($newDate, $dt[$i]["InstallmentDate"]);
			}

			$obj = new LON_installments();
			$obj->RequestID = $RequestID;
			$obj->InstallmentDate = DateModules::AddToGDate($dt[$i]["InstallmentDate"], $days);

			$extraWage = round($dt[$i]["InstallmentAmount"]*$PartObj->CustomerWage*$days/36500);

			$obj->InstallmentAmount = $dt[$i]["InstallmentAmount"]*1 + $extraWage;
			if(!$obj->AddInstallment($pdo))
			{
				$pdo->rollBack ();
				echo Response::createObjectiveResponse(false, "2");
				die();
			}
		}
	}
	else
	{
		$dt = LON_installments::SelectAll("r.RequestID=?", array($RequestID));
		
		$dt2 = ComputePayments($RequestID, $dt);
		$index = 0;
		$ComputeRecord = $dt2[$index++];
		
		$days = 0;
		for($i=0; $i<count($dt); $i++)
		{
			if($dt[$i]["InstallmentID"] < $InstallmentID)
			{
				while($ComputeRecord["InstallmentID"] == $dt[$i]["InstallmentID"])
					$ComputeRecord = $dt2[++$index];
				continue;
			}
			$remain = 0;
			while($ComputeRecord["InstallmentID"] == $dt[$i]["InstallmentID"])
			{
				$remain = $ComputeRecord["remainder"];
				$ComputeRecord = $index+1<count($dt2) ? $dt2[++$index] : null;
			}
			
			$obj = new LON_installments();
			$obj->InstallmentID = $dt[$i]["InstallmentID"];
			$obj->IsDelayed = "YES";
			if(!$obj->EditInstallment($pdo))
			{
				$pdo->rollBack ();
				echo Response::createObjectiveResponse(false, "1");
				die();
			}
			//...........................................

			if($days == 0)
			{
				$newDate = DateModules::shamsi_to_miladi($newDate, "-");
				$days = DateModules::GDateMinusGDate($newDate, $dt[$i]["InstallmentDate"]);
			}

			$obj = new LON_installments();
			$obj->RequestID = $RequestID;
			$obj->InstallmentDate = DateModules::AddToGDate($dt[$i]["InstallmentDate"], $days);

			$extraWage = round($remain*$PartObj->CustomerWage*$days/36500);

			$obj->InstallmentAmount = $dt[$i]["InstallmentAmount"]*1 + $extraWage;
			if(!$obj->AddInstallment($pdo))
			{
				$pdo->rollBack ();
				echo Response::createObjectiveResponse(false, "2");
				die();
			}
		}
	}
	if(ExceptionHandler::GetExceptionCount() > 0)
	{
		$pdo->rollBack ();
		
		print_r(ExceptionHandler::PopAllExceptions());
		echo Response::createObjectiveResponse(false, "");
		die();
	}
	
	$pdo->commit();
	echo Response::createObjectiveResponse(true, "");
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

function SelectReadyToPayParts($returnCount = false){
	
	$dt = PdoDataAccess::runquery("select max(StepID) from WFM_FlowSteps where FlowID=1 AND IsActive='YES'");

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
		where fr.FlowID=1 AND StepID=? AND ActionType='CONFIRM' AND di.ItemID is null 
		group by RequestID",
		array($dt[0][0]));
	
	if($returnCount)
		return count($dt);

	//print_r(ExceptionHandler::PopAllExceptions());
	echo dataReader::getJsonData($dt, count($dt), $_GET["callback"]);
	die();
}

function SelectReceivedRequests($returnCount = false){
	
	$where = "StatusID in (10,50)";
	 
	$branches = FRW_access::GetAccessBranches();
	$where .= " AND BranchID in(" . implode(",", $branches) . ")";
	
	$dt = LON_requests::SelectAll($where);
	
	if($returnCount)
		return $dt->rowCount();
	
	echo dataReader::getJsonData($dt->fetchAll(), $dt->rowCount(), $_GET["callback"]);
	die();
}

function selectRequestStatuses(){
	$dt = PdoDataAccess::runquery("select * from BaseInfo where typeID=5");
	echo dataReader::getJsonData($dt, count($dt), $_GET["callback"]);
	die();
}

//------------------------------------------------

function GetBackPays(){
	
	$dt = LON_BackPays::SelectAll("RequestID=? " . dataReader::makeOrder() , array($_REQUEST["RequestID"]));
	print_r(ExceptionHandler::PopAllExceptions());
	echo dataReader::getJsonData($dt, count($dt), $_GET["callback"]);
	die();
}

function SaveBackPay(){
	
	$obj = new LON_BackPays();
	PdoDataAccess::FillObjectByJsonData($obj, $_POST["record"]);
	
	if($obj->PayType == "9")
		$obj->ChequeStatus = 1;
	
	$pdo = PdoDataAccess::getPdoObject();
	$pdo->beginTransaction();
	
	if(empty($obj->BackPayID))
	{
		$result = $obj->Add($pdo);
		if($obj->PayType == "9")
			RegisterOuterCheque("",$obj,$pdo);
	}
	else
		$result = $obj->Edit($pdo);
	
	if(!$result)
	{
		$pdo->rollback();
		echo Response::createObjectiveResponse(false, "خطا در ثبت ردیف پرداخت");
		die();
	}
	
	$pdo->commit();
	echo Response::createObjectiveResponse(true, "");
	die();
}

function DeletePay(){
	
	$pdo = PdoDataAccess::getPdoObject();
	$pdo->beginTransaction();
	
	$PayObj = new LON_BackPays($_POST["BackPayID"]);
	
	if($PayObj->PayType == "9" && $PayObj->ChequeStatus != "1")
	{
		echo Response::createObjectiveResponse(false, "چک مربوطه تغییر وضعیت یافته است");
		die();
	}
	
	if(!ReturnCustomerPayDoc($PayObj, $pdo))
	{
		//print_r(ExceptionHandler::PopAllExceptions());
		//$pdo->rollBack();		
		echo Response::createObjectiveResponse(false, ExceptionHandler::GetExceptionsToString());
		die();
	}
	
	if($PayObj->PayType == "9")
	{
		if(!ReturnOuterCheque($PayObj, $pdo))
		{
			$pdo->rollBack();
		echo Response::createObjectiveResponse(false, "خطا در حذف سند انتظامی چک");
		die();
		}
	}
	
	if(!LON_BackPays::DeletePay($_POST["BackPayID"], $pdo))
	{
		$pdo->rollBack();
		echo Response::createObjectiveResponse(false, "خطا در حذف ردیف پرداخت");
		die();
	}
	$pdo->commit();
	echo Response::createObjectiveResponse(true, "");
	die();
}

function ComputePayments($RequestID, &$installments){

	$obj = new LON_ReqParts($RequestID);
	if($obj->PayCompute == "installment")
		return ComputePaymentsBaseOnInstallment ($RequestID, $installments);
	
	$returnArr = array();
	$pays = PdoDataAccess::runquery("
		select substr(p.PayDate,1,10) PayDate, sum(PayAmount) PayAmount, sum(PayAmount) FixPayAmount
			from LON_BackPays p
			left join ACC_IncomeCheques i using(IncomeChequeID)
			left join BaseInfo bi on(bi.TypeID=6 AND bi.InfoID=p.PayType)
			where RequestID=? AND 
				if(p.PayType=".BACKPAY_PAYTYPE_CHEQUE.",i.ChequeStatus=".INCOMECHEQUE_VOSUL.",1=1)

			group by substr(PayDate,1,10)
			order by substr(PayDate,1,10)" , array($RequestID));
	$PayRecord = count($pays) == 0 ? null : $pays[0];
	$payIndex = 1;
	$Forfeit = 0;
	for($i=0; $i < count($installments); $i++)
	{
		if($installments[$i]["IsDelayed"] == "YES")
			continue;
		
		$installments[$i]["CurForfeitAmount"] = 0;
		$installments[$i]["ForfeitAmount"] = 0;
		$installments[$i]["ForfeitDays"] = 0;
		$installments[$i]["remainder"] = 0;
		$installments[$i]["FixPayAmount"] = 0;
		$installments[$i]["PayAmount"] = 0;
		$installments[$i]["UsedPayAmount"] = 0;
		$installments[$i]["PayDate"] = '';
		
		if($PayRecord == null)
		{
			$installments[$i]["TotalRemainder"] = $i==0 ? 0 : $installments[$i-1]["TotalRemainder"];
			
			$ToDate = DateModules::Now();
			$amount = $installments[$i]["InstallmentAmount"];
			
			$forfeitDays = DateModules::GDateMinusGDate($ToDate,$installments[$i]["InstallmentDate"]);
			$CurForfeit = round($amount*$installments[$i]["ForfeitPercent"]*$forfeitDays/36500);
			
			if ($installments[$i]["InstallmentDate"] < $ToDate) {
				
				$installments[$i]["ForfeitDays"] = $forfeitDays;
				$Forfeit += $CurForfeit;
				$installments[$i]["ForfeitAmount"] = $Forfeit;
				$installments[$i]["CurForfeitAmount"] = $CurForfeit;
				$installments[$i]["TotalRemainder"] += $amount + $CurForfeit;
				$installments[$i]["remainder"] += $amount;
			}
			else
			{
				$installments[$i]["remainder"] += $amount;
				$installments[$i]["ForfeitDays"] = 0;
				$installments[$i]["ForfeitAmount"] = $Forfeit;
				$installments[$i]["CurForfeitAmount"] = 0;
				$installments[$i]["TotalRemainder"] += $amount;
			}
			$returnArr[] = $installments[$i];
			continue;
		}
		else
			$installments[$i]["TotalRemainder"] = 0;

		$remainder = $installments[$i]["InstallmentAmount"];
		$StartDate = $installments[$i]["InstallmentDate"];

		while(true)
		{
			if($remainder == 0)
				break;	
			$ToDate = $PayRecord == null ? DateModules::Now() : $PayRecord["PayDate"];
			if($PayRecord != null)
			{
				$installments[$i]["FixPayAmount"] = $PayRecord["FixPayAmount"]*1;
				$installments[$i]["PayAmount"] = $PayRecord["PayAmount"]*1;
				$installments[$i]["PayDate"] = $PayRecord["PayDate"];
			}
			else
			{
				$installments[$i]["FixPayAmount"] = 0;
				$installments[$i]["PayAmount"] = 0;
				$installments[$i]["PayDate"] = DateModules::Now();
			}
			if ($StartDate < $ToDate) {
				
				$forfeitDays = DateModules::GDateMinusGDate($ToDate,$StartDate);
				$CurForfeit = round($remainder*$installments[$i]["ForfeitPercent"]*$forfeitDays/36500);
				$installments[$i]["ForfeitDays"] = $forfeitDays;
				$installments[$i]["CurForfeitAmount"] = $CurForfeit;
				$Forfeit += $CurForfeit;
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
			$installments[$i]["UsedPayAmount"] += $Forfeit;
			$Forfeit = 0;			
			
			if($remainder < $PayRecord["PayAmount"]*1)
			{
				$PayRecord["PayAmount"] = $PayRecord["PayAmount"]*1 - $remainder;
				$installments[$i]["UsedPayAmount"] += $remainder;
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
			
			$installments[$i]["TotalRemainder"] = $remainder + $Forfeit;
			$installments[$i]["remainder"] = $remainder;
			
			$PayRecord = $payIndex < count($pays) ? $pays[$payIndex++] : null;
			$returnArr[] = $installments[$i];
		}
	}
	
	return $returnArr;
}

function ComputePaymentsBaseOnInstallment($RequestID, &$installments){
	
	$returnArr = array();
	$pays = PdoDataAccess::runquery("
		select substr(p.PayDate,1,10) PayDate, sum(PayAmount) PayAmount, sum(PayAmount) FixPayAmount
			from LON_BackPays p
			left join ACC_IncomeCheques i using(IncomeChequeID)
			left join BaseInfo bi on(bi.TypeID=6 AND bi.InfoID=p.PayType)
			where RequestID=? AND 
				if(p.PayType=".BACKPAY_PAYTYPE_CHEQUE.",i.ChequeStatus=".INCOMECHEQUE_VOSUL.",1=1)

			group by substr(PayDate,1,10)
			order by substr(PayDate,1,10)" , array($RequestID));
	$PayRecord = count($pays) == 0 ? null : $pays[0];
	$payIndex = 1;
	$Forfeit = 0;
	
	for($i=0; $i < count($installments); $i++)
	{
		if($installments[$i]["IsDelayed"] == "YES")
			continue;
		$forfeitDays = 0;
		$installments[$i]["CurForfeitAmount"] = 0;
		$installments[$i]["ForfeitAmount"] = 0;
		$installments[$i]["ForfeitDays"] = 0;
		$installments[$i]["remainder"] = 0;
		$installments[$i]["FixPayAmount"] = 0;
		$installments[$i]["PayAmount"] = 0;
		$installments[$i]["UsedPayAmount"] = 0;
		$installments[$i]["PayDate"] = '';
		
		if($PayRecord == null)
		{
			$installments[$i]["TotalRemainder"] = $i==0 ? 0 : $installments[$i-1]["TotalRemainder"];
			
			$ToDate = DateModules::Now();
			$amount = $installments[$i]["InstallmentAmount"];
			
			$forfeitDays = DateModules::GDateMinusGDate($ToDate,$installments[$i]["InstallmentDate"]);
			$CurForfeit = round($amount*$installments[$i]["ForfeitPercent"]*$forfeitDays/36500);
			
			if ($installments[$i]["InstallmentDate"] < $ToDate) {
				
				$installments[$i]["ForfeitDays"] = $forfeitDays;
				$Forfeit += $CurForfeit;
				$installments[$i]["ForfeitAmount"] = $Forfeit;
				$installments[$i]["CurForfeitAmount"] = $CurForfeit;
				$installments[$i]["remainder"] = $amount;
				$installments[$i]["TotalRemainder"] += $amount + $CurForfeit ;
			}
			else
			{
				$installments[$i]["ForfeitDays"] = 0;
				$installments[$i]["CurForfeitAmount"] = 0;
				$installments[$i]["ForfeitAmount"] = $Forfeit;
				$installments[$i]["remainder"] = $amount;
				$installments[$i]["TotalRemainder"] += $amount;
			}
			$returnArr[] = $installments[$i];
			continue;
		}
		else
		{
			$installments[$i]["TotalRemainder"] = 0;
		}
		
		$remainder = $installments[$i]["InstallmentAmount"];
		$StartDate = $installments[$i]["InstallmentDate"];

		while(true)
		{
			$ToDate = $PayRecord == null ? DateModules::Now() : $PayRecord["PayDate"];
			if($PayRecord != null)
			{
				$installments[$i]["FixPayAmount"] = $PayRecord["FixPayAmount"]*1;
				$installments[$i]["PayAmount"] = $PayRecord["PayAmount"]*1;
				$installments[$i]["UsedPayAmount"] = $PayRecord["PayAmount"]*1;
				$installments[$i]["PayDate"] = $PayRecord["PayDate"];
			}
			else
			{
				$installments[$i]["FixPayAmount"] = 0;
				$installments[$i]["PayAmount"] = 0;
				$installments[$i]["UsedPayAmount"] = 0;
				$installments[$i]["PayDate"] = DateModules::Now();
			}
			$forfeitDays = DateModules::GDateMinusGDate($ToDate,$StartDate);
			$CurForfeit = round($remainder*$installments[$i]["ForfeitPercent"]*$forfeitDays/36500);
			
			if ($StartDate < $ToDate) {
				
				$installments[$i]["ForfeitDays"] = $forfeitDays;
				$installments[$i]["CurForfeitAmount"] = $CurForfeit;
				$Forfeit += $CurForfeit;
			}		
			
			if($PayRecord == null)
			{
				$installments[$i]["TotalRemainder"] += $CurForfeit;
				$installments[$i]["remainder"] = $remainder;
				$installments[$i]["ForfeitAmount"] = $Forfeit;
				$returnArr[] = $installments[$i];
				break;
			}
			
			if($remainder <= $PayRecord["PayAmount"]*1)
			{
				$PayRecord["PayAmount"] = $PayRecord["PayAmount"]*1 - $remainder;
				$installments[$i]["UsedPayAmount"] = $remainder;
				$remainder = 0;
								
				$installments[$i]["TotalRemainder"] = $Forfeit;
				$installments[$i]["remainder"] = 0;
				$installments[$i]["ForfeitAmount"] = $Forfeit;
				
				if($PayRecord["PayAmount"] == 0)
				{
					$StartDate = max($PayRecord["PayDate"],$installments[$i]["InstallmentDate"]);
					$PayRecord = $payIndex < count($pays) ? $pays[$payIndex++] : null;
				}	
				$returnArr[] = $installments[$i];
				
				break;
			}
						
			$remainder = $remainder - $PayRecord["PayAmount"]*1;
			$StartDate = max($PayRecord["PayDate"],$installments[$i]["InstallmentDate"]);
			
			$installments[$i]["TotalRemainder"] = $remainder + $Forfeit;
			$installments[$i]["remainder"] = $remainder;
			$installments[$i]["ForfeitAmount"] = $Forfeit;
			
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
				$installments[$i]["FixPayAmount"] = $PayRecord["FixPayAmount"];
				$installments[$i]["PayAmount"] = $PayRecord["PayAmount"];
				$installments[$i]["UsedPayAmount"] = $PayRecord["PayAmount"];
				$installments[$i]["PayDate"] = $PayRecord["PayDate"];
				$Forfeit = $Forfeit - $PayRecord["PayAmount"]*1;
				$installments[$i]["ForfeitDays"] = 0;	
				$installments[$i]["TotalRemainder"] = $Forfeit;
				$installments[$i]["CurForfeitAmount"] = 0;
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

function RegisterBackPayDoc(){
	
	$obj = new LON_BackPays($_POST["BackPayID"]);
	$ReqObj = new LON_requests($obj->RequestID);
	$PersonObj = new BSC_persons($ReqObj->ReqPersonID);
	
	$pdo = PdoDataAccess::getPdoObject();
	$pdo->beginTransaction();
	
	if($PersonObj->IsSupporter == "YES")
		$result = RegisterSHRTFUNDCustomerPayDoc(null, $obj, 
			$_POST["CostID"], 
			$_POST["TafsiliID"], 
			$_POST["TafsiliID2"], 
			isset($_POST["CenterAccount"]) ? true : false,
			$_POST["BranchID"],
			$_POST["FirstCostID"],
			$_POST["SecondCostID"], $pdo);
	else
		$result = RegisterCustomerPayDoc(null, $obj, 
			$_POST["CostID"], 
			$_POST["TafsiliID"], 
			$_POST["TafsiliID2"], 
			isset($_POST["CenterAccount"]) ? true : false,
			$_POST["BranchID"],
			$_POST["FirstCostID"],
			$_POST["SecondCostID"], $pdo);
	if(!$result)
	{
		$pdo->rollback();
		//print_r(ExceptionHandler::PopAllExceptions());
		echo Response::createObjectiveResponse(false, "خطا در صدور سند حسابداری");
		die();
	}
	
	$pdo->commit();
	echo Response::createObjectiveResponse(true, "");
	die();
}

function EditBackPayDoc(){
	
	$obj = new LON_BackPays($_POST["BackPayID"]);
	
	$pdo = PdoDataAccess::getPdoObject();
	$pdo->beginTransaction();
	
	$DocID = LON_BackPays::GetAccDoc($obj->BackPayID);
	if($DocID == 0)
	{
		echo Response::createObjectiveResponse(false, "سند مربوطه یافت نشد");
		die();
	}
	$DocObj = new ACC_docs($DocID);
	//------------------  cheque update ------------------------------
	if($obj->PayType == "9")
	{
		if($obj->ChequeStatus != "1")
		{
			echo Response::createObjectiveResponse(false, "وضعیت چک وصول نشده است");
			die();
		}
		if($DocObj->DocStatus != "RAW")
		{
			echo Response::createObjectiveResponse(false, "سند مربوطه تایید شده است");
			die();
		}
		if($obj->IsGroup == "YES")
		{
			echo Response::createObjectiveResponse(false, "امکان ویرایش چک گروهی وجود ندارد");
			die();
		}
		
		$result = RegisterOuterCheque($DocID,$obj,$pdo);
		
		if(!$result)
		{
			$pdo->rollback();
			echo Response::createObjectiveResponse(false, ExceptionHandler::GetExceptionsToString());
			die();
		}

		$pdo->commit();
		echo Response::createObjectiveResponse(true, "");
		die();
	}
	//-----------------------------------------------------------------
	if(!ReturnCustomerPayDoc($obj, $pdo, true))
	{
		echo Response::createObjectiveResponse(false, ExceptionHandler::GetExceptionsToString());
		die();
	}
	
	$ReqObj = new LON_requests($obj->RequestID);
	$PersonObj = new BSC_persons($ReqObj->ReqPersonID);
	if($PersonObj->IsSupporter == "YES")
		$result = RegisterSHRTFUNDCustomerPayDoc($DocObj, $obj, 
				$_POST["CostID"], 
				$_POST["TafsiliID"], 
				$_POST["TafsiliID2"], 
				isset($_POST["CenterAccount"]) ? true : false,
				$_POST["BranchID"],
				$_POST["FirstCostID"],
				$_POST["SecondCostID"], $pdo);
	else
		$result = RegisterCustomerPayDoc($DocObj, $obj, 
				$_POST["CostID"], 
				$_POST["TafsiliID"], 
				$_POST["TafsiliID2"], 
				isset($_POST["CenterAccount"]) ? true : false,
				$_POST["BranchID"],
				$_POST["FirstCostID"],
				$_POST["SecondCostID"], $pdo);
	
	if(!$result)
	{
		$pdo->rollback();
		//print_r(ExceptionHandler::PopAllExceptions());
		echo Response::createObjectiveResponse(false, ExceptionHandler::GetExceptionsToString());
		die();
	}
	
	$pdo->commit();
	echo Response::createObjectiveResponse(true, "");
	die();
}

function GroupSavePay(){
	
	$pdo = PdoDataAccess::getPdoObject();
	$pdo->beginTransaction();
	
	$parts = json_decode($_POST["parts"]);
	
	$FirstPay = true;
	$DocObj = null;
	$sumAmount = 0;
	foreach($parts as $partStr)
	{
		$arr = preg_split("/_/", $partStr);
		$RequestID = $arr[0];
		$PayAmount = $arr[1];

		$obj = new LON_BackPays();
		PdoDataAccess::FillObjectByArray($obj, $_POST);
		$obj->RequestID = $RequestID;
		$obj->PayAmount = $PayAmount;
		$obj->IsGroup = "YES";
		if($obj->PayType == "9")
			$obj->ChequeStatus = 3;
		$obj->Add($pdo);
		
		$ReqObj = new LON_requests($RequestID);
		$PersonObj = new BSC_persons($ReqObj->ReqPersonID);
		if($PersonObj->IsSupporter == "YES")
			$result = RegisterSHRTFUNDCustomerPayDoc($DocObj, $obj, 
				$_POST["CostID"], 
				$_POST["TafsiliID"], 
				$_POST["TafsiliID2"], 
				isset($_POST["CenterAccount"]) ? true : false,
				$_POST["BranchID"],
				$_POST["FirstCostID"],
				$_POST["SecondCostID"], $pdo, true);
		else
			$result = RegisterCustomerPayDoc($DocObj, $obj, 
				$_POST["CostID"], 
				$_POST["TafsiliID"], 
				$_POST["TafsiliID2"], 
				isset($_POST["CenterAccount"]) ? true : false,
				$_POST["BranchID"],
				$_POST["FirstCostID"],
				$_POST["SecondCostID"], $pdo, true);
		
		if(!$result)
		{
			$pdo->rollback();
			print_r(ExceptionHandler::PopAllExceptions());
			echo Response::createObjectiveResponse(false, "خطا در صدور سند حسابداری");
			die();
		}
		
		if($FirstPay)
		{
			$DocID = LON_BackPays::GetAccDoc($obj->BackPayID, $pdo);
			$DocObj = new ACC_docs($DocID, $pdo);
			$FirstPay = false;			
		}
		
		$sumAmount += $PayAmount*1;
	}
	
	if($_POST["PayType"] == "9")
	{
		$obj = new LON_BackPays();
		PdoDataAccess::FillObjectByArray($obj, $_POST);
		$obj->ChequeStatus = "1";
		$obj->PayAmount = $sumAmount;
		
		$result = RegisterOuterCheque($DocID,$obj,$pdo);
		
		$obj->ChequeStatus = "3";
		$result = RegisterOuterCheque($DocID,$obj,$pdo);
	}
	
	$pdo->commit();
	echo Response::createObjectiveResponse(true, "");
	die();
}

//------------------------------------------------

function GetDelayedInstallments($returnData = false){
	
	$ToDate = DateModules::shamsi_to_miladi($_REQUEST["ToDate"]);
	
	$query = "select RequestID,concat_ws(' ',fname,lname,CompanyName) LoanPersonName
			from LON_installments p
			join LON_requests using(RequestID)
			join BSC_persons on(LoanPersonID=PersonID)
			
			where InstallmentDate<:todate AND IsEnded='NO'";
	$param = array(":todate" => $ToDate);
	
	if (isset($_REQUEST['fields']) && isset($_REQUEST['query'])) {
        $field = $_REQUEST['fields'];
		$field = $field == "LoanPersonName" ? "concat_ws(' ',fname,lname,CompanyName)" : $field;
        $query .= ' and ' . $field . ' like :fld';
        $param[':fld'] = '%' . $_REQUEST['query'] . '%';
    }
	
	$query .= " group by RequestID
			order by InstallmentDate desc";
	
	$dt = PdoDataAccess::runquery_fetchMode($query, $param);
	
	$ForfeitDays = !empty($_REQUEST["minDays"]) ? $_REQUEST["minDays"]*1 : 0;
	
	$result = array();
	while($row = $dt->fetch())
	{
		$temp = LON_installments::SelectAll("r.RequestID=? && InstallmentDate<now()" , array($row["RequestID"]));
		$returnArr = ComputePayments($row["RequestID"], $temp);
		
		foreach($returnArr as $row2)
			if(isset($row2["InstallmentID"]) && $row2["InstallmentID"]*1 > 0)
			{
				if($ForfeitDays > 0 && $row2["ForfeitDays"]*1 < $ForfeitDays)
					continue;
				
				if(count($result) > 0 && $result[ count($result)-1 ]["InstallmentID"] == $row2["InstallmentID"])
				{
					if($ForfeitDays == 0)
					{
						if($row2["remainder"]*1 == 0)
							array_pop ($result);
						else
							$result[ count($result)-1 ]["remainder"] = $row2["remainder"];
					}
				}
				else if($row2["remainder"]*1 > 0)
				{
					$row2["LoanPersonName"] = $row["LoanPersonName"];
					$result[] = $row2;
				}
			}
	}
	
	if($returnData)
		return $result;
	
	$cnt = count($result);
	$result = array_slice($result, $_REQUEST["start"], $_REQUEST["limit"]);
	
	echo dataReader::getJsonData($result, $cnt, $_GET["callback"]);
	die();
}

function GetEndedRequests(){
	
	$query = "select rp.RequestID,ReqDate,RequestID,concat_ws(' ',fname,lname,CompanyName) LoanPersonName
			from LON_ReqParts rp
			join LON_requests using(RequestID)
			join BSC_persons on(LoanPersonID=PersonID)
						
			where IsEnded='NO' 
			group by rp.RequestID
			order by rp.RequestID";
	$dt = PdoDataAccess::runquery_fetchMode($query);
	
	$result = array();
	while($row = $dt->fetch())
	{
		$temp = LON_installments::SelectAll("r.RequestID=?" , array($row["RequestID"]));
		$returnArr = ComputePayments($row["RequestID"], $temp);
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

//-------------------------------------------------

function GetPartPayments(){
	
	$dt = LON_payments::Get(" AND RequestID=? " . dataReader::makeOrder() , array($_REQUEST["RequestID"]));
	print_r(ExceptionHandler::PopAllExceptions());
	echo dataReader::getJsonData($dt->fetchAll(), $dt->rowCount(), $_GET["callback"]);
	die();
}

function SavePartPayment(){
	
	$obj = new LON_payments();
	PdoDataAccess::FillObjectByJsonData($obj, $_POST["record"]);
	
	if($obj->PayID > 0)
		$result = $obj->Edit();
	else
		$result = $obj->Add();
	
	echo Response::createObjectiveResponse($result, ExceptionHandler::GetExceptionsToString());
	die();
}

function DeletePayment(){
	
	$obj = new LON_payments($_POST["PayID"]);
	$result = $obj->Remove();
	
	echo Response::createObjectiveResponse($result, "");
	die();
}

function RegPayPartDoc($ReturnMode = false, $pdo = null){
	
	$PayID = $_POST["PayID"];
	$PayObj = new LON_payments($PayID);
	
	//---------- check for previous payments docs registered --------------
	$dt = LON_payments::Get(" AND RequestID=? AND PayDate<? AND DocID=0",
			array($PayObj->RequestID, $PayObj->PayDate));
	if($dt->rowCount() > 0)
	{
		echo Response::createObjectiveResponse(false, "تا سند مراحل قبلی پرداخت صادر نشود قادر به صدور سند این مرحله نمی باشید");
		die();	
	}
	//---------------------------------------------------------------------
	if($pdo == null)
	{
		$pdo = PdoDataAccess::getPdoObject();
		$pdo->beginTransaction();
	}
	$ReqObj = new LON_requests($PayObj->RequestID);
	$partobj = LON_ReqParts::GetValidPartObj($PayObj->RequestID);

	ChangeStatus($PayObj->RequestID, "80", "پرداخت مبلغ " . number_format($PayObj->PayAmount), true, $pdo);
	
	if($partobj->MaxFundWage*1 > 0)
		$partobj->MaxFundWage = round($partobj->MaxFundWage*$PayObj->PayAmount/$partobj->PartAmount);
	
	if($ReqObj->ReqPersonID == "1003")
		$result = RegisterSHRTFUNDPayPartDoc($ReqObj, $partobj, $PayObj, 
				$_POST["BankTafsili"], $_POST["AccountTafsili"], $pdo);
	else
		$result = RegisterPayPartDoc($ReqObj, $partobj, $PayObj, 
				$_POST["BankTafsili"], $_POST["AccountTafsili"], $pdo);
	
	if(!$result)
	{
		$pdo->rollBack();
		echo Response::createObjectiveResponse(false, PdoDataAccess::GetExceptionsToString());
		die();	
	}
	
	$PayObj->DocID = $result;
	if(!$PayObj->Edit($pdo))
	{
		$pdo->rollBack();
		echo Response::createObjectiveResponse(false, PdoDataAccess::GetExceptionsToString());
		die();
	}
	
	if($ReturnMode)
		return true;
	
	$pdo->commit();
	echo Response::createObjectiveResponse(true,"");
	die();
}

function editPayPartDoc(){
	
	$PayID = $_POST["PayID"];
	$PayObj = new LON_payments($PayID);
	$partobj = LON_ReqParts::GetValidPartObj($PayObj->RequestID);
	$ReqObj = new LON_requests($PayObj->RequestID);
	
	$DocObj = new ACC_docs($PayObj->DocID);
	if($DocObj->DocStatus != "RAW")
	{
		echo Response::createObjectiveResponse(false,"سند تایید شده و قابل ویرایش نمی باشد");
		die();
	}
	
	$pdo = PdoDataAccess::getPdoObject();
	$pdo->beginTransaction();
		
	if(!ReturnPayPartDoc($DocObj->DocID, $pdo, false))
	{
		$pdo->rollBack();
		
		echo Response::createObjectiveResponse(false, PdoDataAccess::GetExceptionsToString());
		die();
	}
	if($ReqObj->ReqPersonID == "1003")
		$result = RegisterSHRTFUNDPayPartDoc($ReqObj, $partobj, $PayObj, 
				$_POST["BankTafsili"], $_POST["AccountTafsili"], $pdo, $DocObj->DocID);
	else
		$result = RegisterPayPartDoc($ReqObj, $partobj, $PayObj, 
				$_POST["BankTafsili"], $_POST["AccountTafsili"], $pdo, $DocObj->DocID);

	$pdo->commit();			
	
	echo Response::createObjectiveResponse(true,"");
	die();
}

function RetPayPartDoc($ReturnMode = false, $pdo = null){
	
	if(empty($_POST["PayID"]))
	{
		echo Response::createObjectiveResponse(false, "درخواست نامعتبر");
		die();
	}
	$PayID = $_POST["PayID"];
	$PayObj = new LON_payments($PayID);
		
	//------------- check for Acc doc confirm -------------------
	$temp = PdoDataAccess::runquery("select DocStatus 
		from ACC_DocItems join ACC_docs using(DocID) where SourceType=" . DOCTYPE_LOAN_PAYMENT . " AND 
		DocID=?", array($PayObj->DocID));
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
		array($CostCode_todiee, $PayObj->DocID));
	if(count($temp) > 0)
	{
		$dt = PdoDataAccess::runquery("select * from ACC_DocItems where CostID=? AND DebtorAmount>0 
			AND SourceType=? AND SourceID=?",
			array($CostCode_todiee, DOCTYPE_LOAN_PAYMENT, $PayObj->RequestID));
		if(count($dt) > 0)
		{
			echo Response::createObjectiveResponse(false, "به دلیل اینکه این سند اولین سند پرداخت می باشد و بعد از آن اسناد پرداخت دیگری صادر شده است" . 
				" قادر به برگشت نمی باشید. <br> برای برگشت ابتدا کلیه اسناد بعدی را برگشت بزنید");
			die();
		}
	}
	//-----------------------------------------------------------
	if($pdo == null)
	{
		$pdo = PdoDataAccess::getPdoObject();
		$pdo->beginTransaction();
	}
	
	if(!ReturnPayPartDoc($PayObj->DocID, $pdo, !$ReturnMode))
	{
		if($ReturnMode)
			return false;
		$pdo->rollBack();
		echo Response::createObjectiveResponse(false, PdoDataAccess::GetExceptionsToString());
		die();
	}

	$PayObj->DocID = 0;
	if(!$PayObj->Edit($pdo))
	{
		if($ReturnMode)
			return false;
		$pdo->rollBack();
		echo Response::createObjectiveResponse(false, PdoDataAccess::GetExceptionsToString());
		die();
	}
	
	ChangeStatus($PayObj->RequestID, "90", "", true, $pdo);
	
	if($ReturnMode)
		return true;
	
	$pdo->commit();	
	
	echo Response::createObjectiveResponse(true, "");
	die();
}

//-------------------------------------------------

function SelectAllMessages($returnCount = false){
	
	$where = "";
	$param = array();
	
	if(!empty($_REQUEST["RequestID"]))
	{
		$where .= " AND RequestID=?";
		$param[] = $_REQUEST["RequestID"];
	}
	
	if(!empty($_REQUEST["MsgStatus"]))
	{
		$where .= " AND MsgStatus=?";
		$param[] = $_REQUEST["MsgStatus"];
	}
	$res = LON_messages::Get($where, $param, dataReader::makeOrder());
	
	if($returnCount)
		return $res->rowCount();
	
	print_r(ExceptionHandler::PopAllExceptions());
	$cnt = $res->rowCount();
	$res = PdoDataAccess::fetchAll($res, $_GET["start"], $_GET["limit"]);
	echo dataReader::getJsonData($res, $cnt, $_GET["callback"]);
	die();
}

function saveMessage(){
	
	$obj = new LON_messages();
	
	if(isset($_POST["record"]))
		PdoDataAccess::FillObjectByJsonData($obj, $_POST["record"]);
	else
	{
		PdoDataAccess::FillObjectByArray($obj, $_POST);
		$obj->MsgStatus = "DONE";
	}
	
	if(isset($_POST["DoneDesc"]))
		$obj->DoneDate = PDONOW;
	
	if($obj->MessageID != "")
		$result = $obj->Edit();
	else
	{
		$obj->RegPersonID = $_SESSION["USER"]["PersonID"];
		$obj->CreateDate = PDONOW;
		$result = $obj->Add();
	}
	print_r(ExceptionHandler::PopAllExceptions());
	Response::createObjectiveResponse($result, "");
	die();
}

function removeMessage(){
	
	$obj = new LON_messages($_POST["MessageID"]);
	if($obj->MsgStatus == "RAW")
		$result = $obj->Remove();
	else
		$result = false;
	
	Response::createObjectiveResponse($result, "");
	die();
}

function ConfirmRequest(){
	
	$RequestID = $_POST["RequestID"];
	
	PdoDataAccess::runquery("update LON_requests set IsConfirm='YES' where RequestID=?", array($RequestID));
	//print_r(ExceptionHandler::PopAllExceptions());
	echo Response::createObjectiveResponse(true, "");
	die();
}

//-------------------------------------------------

function GetChequeStatuses(){
	
	$dt = PdoDataAccess::runquery("select * from BaseInfo where typeID=16");
	echo dataReader::getJsonData($dt, count($dt), $_GET["callback"]);
	die();
}

function GetPayTypes(){
	
	$dt = PdoDataAccess::runquery("select * from BaseInfo where typeID=6");
	echo dataReader::getJsonData($dt, count($dt), $_GET["callback"]);
	die();
}

function GetBanks(){
	
	$dt = PdoDataAccess::runquery("select * from ACC_banks");
	echo dataReader::getJsonData($dt, count($dt), $_GET["callback"]);
	die();
}

//............................................

function GetEvents(){
	
	$temp = LON_events::Get("AND RequestID=?", array($_REQUEST["RequestID"]));
	//print_r(ExceptionHandler::PopAllExceptions());
	$res = $temp->fetchAll();
	echo dataReader::getJsonData($res, $temp->rowCount(), $_GET["callback"]);
	die();
}

function SaveEvents(){
	
	$obj = new LON_events();
	PdoDataAccess::FillObjectByJsonData($obj, $_POST["record"]);
	
	if(empty($obj->EventID))
	{
		$obj->RegPersonID = $_SESSION["USER"]["PersonID"];
		$result = $obj->Add();
	}
	else
		$result = $obj->Edit();
	
	echo Response::createObjectiveResponse($result, ExceptionHandler::GetExceptionsToString());
	die();
}

function DeleteEvents(){
	
	$obj = new LON_events();
	$obj->EventID = $_POST["EventID"];
	$result = $obj->Remove();
	echo Response::createObjectiveResponse($result, ExceptionHandler::GetExceptionsToString());
	die();	
}

//------------------------------------------------

function GetCosts(){
	
	$temp = LON_costs::Get("AND RequestID=?", array($_REQUEST["RequestID"]));
	$res = $temp->fetchAll();
	echo dataReader::getJsonData($res, $temp->rowCount(), $_GET["callback"]);
	die();
}

function SaveCosts(){
	
	$obj = new LON_costs();
	PdoDataAccess::FillObjectByJsonData($obj, $_POST["record"]);
	
	$pdo = PdoDataAccess::getPdoObject();
	$pdo->beginTransaction();
	
	if(empty($obj->CostID))
	{
		if(!$obj->Add($pdo))
		{
			echo Response::createObjectiveResponse(false, "خطا در ثبت هزینه");
			die();
		}
		if(!RegisterLoanCost($obj, $_POST["CostID"], $_POST["TafsiliID"], $_POST["TafsiliID2"], $pdo))
		{
			echo Response::createObjectiveResponse(false, ExceptionHandler::GetExceptionsToString());
			die();
		}
	}
	else
	{
		if(!$obj->Edit($pdo))
		{
			echo Response::createObjectiveResponse(false, "خطا در ویرایش هزینه");
			die();
		}
	}
	
	$pdo->commit();
	echo Response::createObjectiveResponse(true, "");
	die();
}

function DeleteCosts(){
	
	$obj = new LON_costs($_POST["CostID"]);
	
	$DocRecord = $obj->GetAccDoc();
	if($DocRecord)
	{
		if($DocRecord["DocStatus"] != "RAW")
		{
			echo Response::createObjectiveResponse(false, "سند مربوطه تایید شده و قابل حذف نمی باشد");
			die();	
		}
		
		ACC_docs::Remove($DocRecord["DocID"]);
	}
	
	$result = $obj->Remove();
	echo Response::createObjectiveResponse($result, ExceptionHandler::GetExceptionsToString());
	die();	
}

?>
