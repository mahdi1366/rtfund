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

function FindCostID($costCode)
{
	$dt = PdoDataAccess::runquery("select * from ACC_CostCodes where IsActive='YES' AND CostCode=?",
		array($costCode));
	return $dt[0]["CostID"];
}

function FindTafsiliID($TafsiliCode, $TafsiliType)
{
	$dt = PdoDataAccess::runquery("select * from ACC_tafsilis "
			. "where IsActive='YES' AND TafsiliCode=? AND TafsiliType=?",
		array($TafsiliCode, $TafsiliType));
	
	return $dt[0]["TafsiliID"];
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
	
	$CycleID = substr(DateModules::miladi_to_shamsi($PartObj->PartDate), 0 , 4);
	
	
	//---------------- add doc header --------------------
	$obj = new ACC_docs();
	$obj->RegDate = PDONOW;
	$obj->regPersonID = $_SESSION['USER']["PersonID"];
	$obj->DocDate = PDONOW;
	$obj->CycleID = $CycleID;
	$obj->BranchID = $ReqObj->BranchID;
	$obj->DocType = DOCTYPE_LOAN_PAYMENT;
	$obj->description = "پرداخت مرحله " . $PartObj->PartDesc . " وام شماره " . $ReqObj->RequestID;
	
	if(!$obj->Add($pdo))
		return false;
	
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
	
	//----------------- add Doc items ------------------------
		
	$itemObj = new ACC_DocItems();
	$itemObj->DocID = $obj->DocID;
	$itemObj->CostID = FindCostID(10110);
	$itemObj->DebtorAmount = $PartObj->PartAmount;
	$itemObj->CreditorAmount = 0;
	$itemObj->TafsiliType = TAFTYPE_PERSONS;
	$itemObj->TafsiliID = FindTafsiliID($ReqObj->LoanPersonID, TAFTYPE_PERSONS);
	$itemObj->Tafsili2Type = TAFTYPE_PERSONS;
	$itemObj->Tafsili2ID = FindTafsiliID($ReqObj->ReqPersonID, TAFTYPE_PERSONS);
	$itemObj->locked = "YES";
	$itemObj->SourceType = "PAY_LOAN_PART";
	$itemObj->SourceID = $ReqObj->RequestID;
	$itemObj->SourceID2 = $PartObj->PartID;
	if(!$itemObj->Add($pdo))
		return false;
	
	// ---- bank ----
	$itemObj = new ACC_DocItems();
	$itemObj->DocID = $obj->DocID;
	$itemObj->CostID = FindCostID(10101);
	$itemObj->DebtorAmount = 0;
	$itemObj->CreditorAmount = $PartObj->PartAmount - $TotalDelay;
	$itemObj->TafsiliType = TAFTYPE_BANKS;
	$itemObj->locked = "YES";
	$itemObj->SourceType = "PAY_LOAN_PART";
	$itemObj->SourceID = $PartObj->PartID;
	if(!$itemObj->Add($pdo))
		return false;
	
	//---- delay -----
	if($TotalDelay > 0)
	{
		unset($itemObj->ItemID);
		unset($itemObj->Tafsili2Type);
		unset($itemObj->Tafsili2ID);
		$itemObj->CostID = FindCostID(30310);
		$itemObj->DebtorAmount = 0;
		$itemObj->CreditorAmount = $TotalDelay;
		$itemObj->TafsiliType = TAFTYPE_YEARS;
		$itemObj->details = "کارمزد دوره تنفس";
		$itemObj->TafsiliID = FindTafsiliID($curYear, TAFTYPE_YEARS);
		if(!$itemObj->Add($pdo))
			return false;
	}	
	//---- کارمزد-----
	$amountArr = array(
		$curYear => $year1, 
		$curYear+1 => $year2, 
		$curYear+2 => $year3, 
		$curYear+3 => $year4);
	
	foreach($amountArr as $year => $amount)
	{
		if($amount == 0 || $amount == "")
			continue;
		
		unset($itemObj->ItemID);
		$itemObj->details = "";
		unset($itemObj->Tafsili2Type);
		unset($itemObj->Tafsili2ID);
		$itemObj->CostID = FindCostID(30310);
		$itemObj->DebtorAmount = 0;
		$itemObj->CreditorAmount = round($amount);
		$itemObj->TafsiliType = TAFTYPE_YEARS;
		$itemObj->TafsiliID = FindTafsiliID($year, TAFTYPE_YEARS);
		if(!$itemObj->Add($pdo))
			return false;
		
		unset($itemObj->ItemID);
		unset($itemObj->Tafsili2Type);
		unset($itemObj->Tafsili2ID);
		$itemObj->CostID = FindCostID(20102);
		$itemObj->DebtorAmount = round($amount);
		$itemObj->CreditorAmount = 0;
		$itemObj->TafsiliType = TAFTYPE_PERSONS;
		$itemObj->TafsiliID = FindTafsiliID($ReqObj->ReqPersonID, TAFTYPE_PERSONS);
		if(!$itemObj->Add($pdo))
			return false;
	}
	
	//---- کسر از سپرده -----
	unset($itemObj->ItemID);
	unset($itemObj->Tafsili2Type);
	unset($itemObj->Tafsili2ID);
	$itemObj->CostID = FindCostID(20102);
	$itemObj->DebtorAmount = $PartObj->PartAmount;
	$itemObj->CreditorAmount = 0;
	$itemObj->TafsiliType = TAFTYPE_PERSONS;
	$itemObj->TafsiliID = FindTafsiliID($ReqObj->ReqPersonID, TAFTYPE_PERSONS);
	if(!$itemObj->Add($pdo))
		return false;

	unset($itemObj->ItemID);
	unset($itemObj->Tafsili2Type);
	unset($itemObj->Tafsili2ID);
	$itemObj->CostID = FindCostID(30660);
	$itemObj->DebtorAmount = 0;
	$itemObj->CreditorAmount = $PartObj->PartAmount;
	$itemObj->TafsiliType = TAFTYPE_PERSONS;
	$itemObj->TafsiliID = FindTafsiliID($ReqObj->ReqPersonID, TAFTYPE_PERSONS);
	if(!$itemObj->Add($pdo))
		return false;
	
	//------ ایجاد چک ------
	$chequeObj = new ACC_DocChecks();
	$chequeObj->DocID = $obj->DocID;
	$chequeObj->CheckDate = $PartObj->PartDate;
	$chequeObj->amount = $PartObj->PartAmount - $TotalDelay;
	$chequeObj->TafsiliID = FindTafsiliID($ReqObj->LoanPersonID, TAFTYPE_PERSONS);
	$chequeObj->description = " پرداخت " . $PartObj->PartDesc . " وام شماره " . $ReqObj->RequestID;
	$chequeObj->Add($pdo);
	//--------------------------------------------------------
	
	/*$PersonObj = new BSC_persons($ReqObj->ReqPersonID);
	if($PersonObj->IsAgent)
	{
		
	}*/
	
	//---------------------------------------------------------
	print_r(ExceptionHandler::PopAllExceptions());
	if(ExceptionHandler::GetExceptionCount() > 0)
		return false;
		
	
	return true;
}

?>