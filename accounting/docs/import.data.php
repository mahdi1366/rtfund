<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 94.06
//-----------------------------

require_once '../header.inc.php';
require_once getenv("DOCUMENT_ROOT") . '/accounting/definitions.inc.php';
require_once 'doc.class.php';
require_once inc_dataReader;
require_once inc_response;

$task = isset($_POST ["task"]) ? $_POST ["task"] : (isset($_GET ["task"]) ? $_GET ["task"] : "");

switch ($task) {
	
}

function RegisterPayPartDoc($ReqObj, $PartObj, $pdo){
		
	/*@var $ReqObj LON_requests */
	/*@var $PartObj LON_ReqParts */
	
	require_once '../../loan/request/request.data.php';
	
	/*$LocalNo = $_POST["LocalNo"];
	if($LocalNo != "")
	{
		$dt = PdoDataAccess::runquery("select * from ACC_docs 
			where BranchID=? AND CycleID=? AND LocalNo=?" , 

			array($_SESSION["accounting"]["BranchID"], 
				$_SESSION["accounting"]["CycleID"], 
				$LocalNo));

		if(count($dt) > 0)
		{
			echo Response::createObjectiveResponse(false, "شماره برگه وارد شده موجود می باشد");
			die();
		}
	}*/
	
	$CycleID = substr($PartObj->PartDate, 0 , 4);
	
	
	//---------------- add doc header --------------------
	$obj = new ACC_docs();
	$obj->RegDate = PDONOW;
	$obj->regPersonID = $_SESSION['USER']["PersonID"];
	$obj->DocDate = PDONOW;
	$obj->CycleID = $CycleID;
	$obj->BranchID = $ReqObj->BranchID;
	$obj->DocType = DOCTYPE_LOAN_PAYMENT;
	
	$result = $obj->Add($pdo);

	if (!$result) {
		$pdo->rollBack();
		print_r(ExceptionHandler::PopAllExceptions());
		echo Response::createObjectiveResponse(false, "");
		die();
	}
	
	//----------------- add Doc items ------------------------
		
	$itemObj = new ACC_DocItems();
	$itemObj->DocID = $obj->DocID;
	$itemObj->CostID = 1;
	$itemObj->DebtorAmount = $PartObj->PartAmount;
	$itemObj->CreditorAmount = 0;
	$itemObj->TafsiliType = TAFTYPE_PERSONS;
	$itemObj->TafsiliID = $ReqObj->LoanPersonID;
	$itemObj->locked = "YES";
	$itemObj->SourceType = "PAY_LOAN_PART";
	$itemObj->SourceID = $PartObj->PartID;
	if(!$itemObj->Add($pdo))
	{
		$pdo->rollBack();
		echo Response::createObjectiveResponse(false, "");
		die();
	}
	
	//--------------------------------------------------------
	
	$YearMonths = 12;
	if($PartObj->IntervalType == "DAY")
		$YearMonths = floor(365/$PartObj->PayInterval);
	$TotalWage = round(ComputeWage($PartObj->PartAmount, $PartObj->CustomerWage/100, 
			$PartObj->InstallmentCount, $YearMonths));	
	$FundFactor = $PartObj->FundWage/$PartObj->CustomerWage;
	
	$year1 = $FundFactor*YearWageCompute($PartObj, $TotalWage, 1, $YearMonths);
	$year2 = $FundFactor*YearWageCompute($PartObj, $TotalWage, 2, $YearMonths);
	$year3 = $FundFactor*YearWageCompute($PartObj, $TotalWage, 3, $YearMonths);
	$year4 = $FundFactor*YearWageCompute($PartObj, $TotalWage, 4, $YearMonths);
	
	$TotalDelay = round($PartObj->PartAmount*$PartObj->CustomerWage*$PartObj->DelayMonths/1200);
	$curYear = substr(DateModules::miladi_to_shamsi($PartObj->PartDate), 0, 4)*1;
	//--------------------------------------------------------
	
	$amountArr = array(
		$curYear => $TotalDelay, 
		$curYear => $year1, 
		$curYear+1 => $year2, 
		$curYear+2 => $year3, 
		$curYear+3 => $year4);
	
	foreach($amountArr as $TafsiliID => $amount)
	{
		if($amount == 0 || $amount = "")
			continue;
		
		unset($itemObj->ItemID);
		$itemObj->CostID = 1;
		$itemObj->DebtorAmount = 0;
		$itemObj->CreditorAmount = $amount;
		$itemObj->TafsiliType = TAFTYPE_YEARS;
		$itemObj->TafsiliID = $TafsiliID;
		if(!$itemObj->Add($pdo))
		{
			$pdo->rollBack();
			echo Response::createObjectiveResponse(false, "");
			die();
		}
	}
	
	//--------------------------------------------------------
	
	$PersonObj = new BSC_persons($ReqObj->ReqPersonID);
	if($PersonObj->IsAgent)
	{
		
	}
	
	//---------------------------------------------------------
	
	if(ExceptionHandler::GetExceptionCount() > 0)
	{
		$pdo->rollBack();
		echo Response::createObjectiveResponse(false, "");
		die();
	}
	
	if(PdoDataAccess::AffectedRows($pdo) == 0)
	{
		$pdo->rollBack();
		echo Response::createObjectiveResponse(false, "ردیفی برای صدور سند اختتامیه یافت نشد");
		die();
	}
	
	$pdo->commit();
	echo Response::createObjectiveResponse(true, "");
	die();
}

?>