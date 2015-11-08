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

function FindCostID($costCode){
	
	$dt = PdoDataAccess::runquery("select * from ACC_CostCodes where IsActive='YES' AND CostCode=?",
		array($costCode));
	
	return count($dt) == 0? false : $dt[0]["CostID"];
}

function FindTafsiliID($TafsiliCode, $TafsiliType){
	
	$dt = PdoDataAccess::runquery("select * from ACC_tafsilis "
			. "where IsActive='YES' AND TafsiliCode=? AND TafsiliType=?",
		array($TafsiliCode, $TafsiliType));
	
	return count($dt) == 0? false : $dt[0]["TafsiliID"];
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
	
	//------------------ find tafsilis ---------------
	$LoanPersonTafsili = FindTafsiliID($ReqObj->LoanPersonID, TAFTYPE_PERSONS);
	if(!$LoanPersonTafsili)
	{
		ExceptionHandler::PushException("تفصیلی مربوطه یافت نشد.[" . $ReqObj->LoanPersonID . "]");
		return false;
	}
	$ReqPersonTafsili = FindTafsiliID($ReqObj->ReqPersonID, TAFTYPE_PERSONS);
	if(!$ReqPersonTafsili)
	{
		ExceptionHandler::PushException("تفصیلی مربوطه یافت نشد.[" . $ReqObj->ReqPersonID . "]");
		return false;
	}
	
	$amountArr = array();
	$curYearTafsili = FindTafsiliID($curYear, TAFTYPE_YEARS);
	if(!$curYear)
	{
		ExceptionHandler::PushException("تفصیلی مربوطه یافت نشد.[" . $curYear . "]");
		return false;
	}
	$amountArr[$curYearTafsili] = $year1;
	
	if($year2 > 0)
	{
		$Year2Tafsili = FindTafsiliID($curYear+1, TAFTYPE_YEARS);
		if(!$Year2Tafsili)
		{
			ExceptionHandler::PushException("تفصیلی مربوطه یافت نشد.[" . ($curYear+1) . "]");
			return false;
		}
		$amountArr[$Year2Tafsili] = $year2;
	}
	if($year3 > 0)
	{
		$Year3Tafsili = FindTafsiliID($curYear+2, TAFTYPE_YEARS);
		if(!$Year3Tafsili)
		{
			ExceptionHandler::PushException("تفصیلی مربوطه یافت نشد.[" . ($curYear+2) . "]");
			return false;
		}
		$amountArr[$Year3Tafsili] = $year3;
	}
	if($year4 > 0)
	{
		$Year4Tafsili = FindTafsiliID($curYear+3, TAFTYPE_YEARS);
		if(!$Year4Tafsili)
		{
			ExceptionHandler::PushException("تفصیلی مربوطه یافت نشد.[" . ($curYear+3) . "]");
			return false;
		}
		$amountArr[$Year4Tafsili] = $year4;
	}
	//----------------- add Doc items ------------------------
		
	$itemObj = new ACC_DocItems();
	$itemObj->DocID = $obj->DocID;
	$itemObj->CostID = FindCostID(10110);
	$itemObj->DebtorAmount = $PartObj->PartAmount;
	$itemObj->CreditorAmount = 0;
	$itemObj->TafsiliType = TAFTYPE_PERSONS;
	$itemObj->TafsiliID = $LoanPersonTafsili;
	$itemObj->Tafsili2Type = TAFTYPE_PERSONS;
	$itemObj->Tafsili2ID = $ReqPersonTafsili;
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
		$itemObj->TafsiliID = $curYearTafsili;
		if(!$itemObj->Add($pdo))
			return false;
	}	
	//---- کارمزد-----
	foreach($amountArr as $yearTafsili => $amount)
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
		$itemObj->TafsiliID = $yearTafsili;
		if(!$itemObj->Add($pdo))
			return false;
		
		unset($itemObj->ItemID);
		unset($itemObj->Tafsili2Type);
		unset($itemObj->Tafsili2ID);
		$itemObj->CostID = FindCostID(20102);
		$itemObj->DebtorAmount = round($amount);
		$itemObj->CreditorAmount = 0;
		$itemObj->TafsiliType = TAFTYPE_PERSONS;
		$itemObj->TafsiliID = $ReqPersonTafsili;
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
	$itemObj->TafsiliID = $ReqPersonTafsili;
	if(!$itemObj->Add($pdo))
		return false;

	unset($itemObj->ItemID);
	unset($itemObj->Tafsili2Type);
	unset($itemObj->Tafsili2ID);
	$itemObj->CostID = FindCostID(30660);
	$itemObj->DebtorAmount = 0;
	$itemObj->CreditorAmount = $PartObj->PartAmount;
	$itemObj->TafsiliType = TAFTYPE_PERSONS;
	$itemObj->TafsiliID = $ReqPersonTafsili;
	if(!$itemObj->Add($pdo))
		return false;
	
	//------ ایجاد چک ------
	$chequeObj = new ACC_DocChecks();
	$chequeObj->DocID = $obj->DocID;
	$chequeObj->CheckDate = $PartObj->PartDate;
	$chequeObj->amount = $PartObj->PartAmount - $TotalDelay;
	$chequeObj->TafsiliID = $LoanPersonTafsili;
	$chequeObj->description = " پرداخت " . $PartObj->PartDesc . " وام شماره " . $ReqObj->RequestID;
	$chequeObj->Add($pdo);
	//--------------------------------------------------------
	
	/*$PersonObj = new BSC_persons($ReqObj->ReqPersonID);
	if($PersonObj->IsAgent)
	{
		
	}*/
	
	//---------------------------------------------------------
	if(ExceptionHandler::GetExceptionCount() > 0)
		return false;
		
	
	return true;
}

?>