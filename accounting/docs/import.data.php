<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 94.06
//-----------------------------

require_once '../header.inc.php';
require_once getenv("DOCUMENT_ROOT") . '/accounting/definitions.inc.php';
require_once getenv("DOCUMENT_ROOT") . '/framework/person/persons.class.php';
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

//---------------------------------------------------------------

function RegisterPayPartDoc($ReqObj, $PartObj, $pdo){
		
	require_once '../../loan/request/request.data.php';
	
	/*@var $ReqObj LON_requests */
	/*@var $PartObj LON_ReqParts */
	
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
	
	//------------- get CostCodes --------------------
	$LoanObj = new LON_loans($ReqObj->LoanID);
	$CostCode_Loan = FindCostID("110" . "-" . $LoanObj->_BlockCode);
	$CostCode_wage = FindCostID("750" . "-" . $LoanObj->_BlockCode);
	$CostCode_deposite = FindCostID("210-01");
	$CostCode_bank = FindCostID("101");
	$CostCode_pardakhtani = FindCostID("200");
	$CostCode_guaranteeAmount = FindCostID("904-02");
	$CostCode_guaranteeCount = FindCostID("904-01");
	$CostCode_guaranteeAmount2 = FindCostID("905-02");
	$CostCode_guaranteeCount2 = FindCostID("905-01");
	//------------------------------------------------
	
	$CycleID = substr(DateModules::miladi_to_shamsi($PartObj->PartDate), 0 , 4);
	
	//---------------- add doc header --------------------
	$obj = new ACC_docs();
	$obj->RegDate = PDONOW;
	$obj->regPersonID = $_SESSION['USER']["PersonID"];
	$obj->DocDate = PDONOW;
	$obj->CycleID = $CycleID;
	$obj->BranchID = $ReqObj->BranchID;
	$obj->DocType = DOCTYPE_LOAN_PAYMENT;
	$obj->description = "پرداخت " . $PartObj->PartDesc . " وام شماره " . $ReqObj->RequestID;
	
	if(!$obj->Add($pdo))
	{
		ExceptionHandler::PushException("خطا در ایجاد سند");
		return false;
	}
	
	//--------------------------------------------------------
	
	$YearMonths = 12;
	if($PartObj->IntervalType == "DAY")
		$YearMonths = floor(365/$PartObj->PayInterval);
	$TotalWage = round(ComputeWage($PartObj->PartAmount, $PartObj->CustomerWage/100, 
			$PartObj->InstallmentCount, $YearMonths));	
	$FundFactor = $PartObj->CustomerWage*1 == 0 ? 0 : $PartObj->FundWage/$PartObj->CustomerWage;
	
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
	
	$LoanMode = "";
	$PersonObj = new BSC_persons($ReqObj->ReqPersonID);
	if($PersonObj->IsAgent == "YES")
		$LoanMode = "Agent";
	if($PersonObj->IsStaff == "YES" && $ReqObj->SupportPersonID > 0)
		$LoanMode = "Supporter";
	if($PersonObj->IsCustomer == "YES")
		$LoanMode = "Customer";
	
	if($LoanMode == "Agent")
	{
		$ReqPersonTafsili = FindTafsiliID($ReqObj->ReqPersonID, TAFTYPE_PERSONS);
		if(!$ReqPersonTafsili)
		{
			ExceptionHandler::PushException("تفصیلی مربوطه یافت نشد.[" . $ReqObj->ReqPersonID . "]");
			return false;
		}
	}
	if($LoanMode == "Supporter")
	{
		$SupporterTafsili = FindTafsiliID($ReqObj->SupportPersonID, TAFTYPE_PERSONS);
		if(!$SupporterTafsili)
		{
			ExceptionHandler::PushException("تفصیلی مربوطه یافت نشد.[" . $ReqObj->SupportPersonID . "]");
			return false;
		}
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
	$itemObj->CostID = $CostCode_Loan;
	$itemObj->DebtorAmount = $PartObj->PartAmount;
	$itemObj->CreditorAmount = 0;
	$itemObj->TafsiliType = TAFTYPE_PERSONS;
	$itemObj->TafsiliID = $LoanPersonTafsili;
	if($LoanMode == "Agent")
	{
		$itemObj->Tafsili2Type = TAFTYPE_PERSONS;
		$itemObj->Tafsili2ID = $ReqPersonTafsili;
	}
	if($LoanMode == "Supporter")
	{
		$itemObj->Tafsili2Type = TAFTYPE_PERSONS;
		$itemObj->Tafsili2ID = $SupporterTafsili;
	}
	$itemObj->locked = "YES";
	$itemObj->SourceType = DOCTYPE_LOAN_PAYMENT;
	$itemObj->SourceID = $ReqObj->RequestID;
	$itemObj->SourceID2 = $PartObj->PartID;
	if(!$itemObj->Add($pdo))
	{
		ExceptionHandler::PushException("خطا در ایجاد ردیف سند");
		return false;
	}
	
	//---- delay -----
	if($TotalDelay > 0)
	{
		unset($itemObj->ItemID);
		unset($itemObj->Tafsili2Type);
		unset($itemObj->Tafsili2ID);
		$itemObj->CostID = $CostCode_wage;
		$itemObj->DebtorAmount = 0;
		$itemObj->CreditorAmount = $TotalDelay;
		$itemObj->TafsiliType = TAFTYPE_YEARS;
		$itemObj->details = "کارمزد دوره تنفس";
		$itemObj->TafsiliID = $curYearTafsili;
		if(!$itemObj->Add($pdo))
		{
			ExceptionHandler::PushException("خطا در ایجاد ردیف سند");
			return false;
		}
	}	
	//---- کارمزد-----
	$WageSum = 0;
	foreach($amountArr as $yearTafsili => $amount)
	{
		if($amount == 0 || $amount == "")
			continue;
		
		unset($itemObj->ItemID);
		$itemObj->details = "";
		unset($itemObj->Tafsili2Type);
		unset($itemObj->Tafsili2ID);
		$itemObj->CostID = $CostCode_wage;
		$itemObj->DebtorAmount = 0;
		$itemObj->CreditorAmount = round($amount);
		$itemObj->TafsiliType = TAFTYPE_YEARS;
		$itemObj->TafsiliID = $yearTafsili;
		if(!$itemObj->Add($pdo))
		{
			ExceptionHandler::PushException("خطا در ایجاد ردیف سند");
			return false;
		}
		
		if($LoanMode == "Agent")
		{
			unset($itemObj->ItemID);
			unset($itemObj->Tafsili2Type);
			unset($itemObj->Tafsili2ID);
			$itemObj->CostID = $CostCode_deposite;
			$itemObj->DebtorAmount = round($amount);
			$itemObj->CreditorAmount = 0;
			$itemObj->TafsiliType = TAFTYPE_PERSONS;
			$itemObj->TafsiliID = $ReqPersonTafsili;
			if(!$itemObj->Add($pdo))
			{
				ExceptionHandler::PushException("خطا در ایجاد ردیف سند");
				return false;
			}
		}
		else
			$WageSum += round($amount);
	}
	// ---- bank ----
	$itemObj = new ACC_DocItems();
	$itemObj->DocID = $obj->DocID;
	$itemObj->CostID = $CostCode_bank;
	$itemObj->DebtorAmount = 0;
	if($LoanMode == "Agent")
		$itemObj->CreditorAmount = $PartObj->PartAmount - $TotalDelay - $WageSum;
	else
		$itemObj->CreditorAmount = $PartObj->PartAmount - $TotalDelay;
	$itemObj->TafsiliType = TAFTYPE_BANKS;
	$itemObj->locked = "YES";
	$itemObj->SourceType = DOCTYPE_LOAN_PAYMENT;
	$itemObj->SourceID = $PartObj->PartID;
	if(!$itemObj->Add($pdo))
	{
		ExceptionHandler::PushException("خطا در ایجاد ردیف سند");
		return false;
	}
	
	if($LoanMode == "Agent")
	{
		//---- کسر از سپرده -----
		unset($itemObj->ItemID);
		unset($itemObj->Tafsili2Type);
		unset($itemObj->Tafsili2ID);
		$itemObj->CostID = $CostCode_deposite;
		$itemObj->DebtorAmount = $PartObj->PartAmount + $WageSum;
		$itemObj->CreditorAmount = 0;
		$itemObj->TafsiliType = TAFTYPE_PERSONS;
		$itemObj->TafsiliID = $ReqPersonTafsili;
		if(!$itemObj->Add($pdo))
		{
			ExceptionHandler::PushException("خطا در ایجاد ردیف سند");
			return false;
		}

		unset($itemObj->ItemID);
		unset($itemObj->Tafsili2Type);
		unset($itemObj->Tafsili2ID);
		$itemObj->CostID = $CostCode_pardakhtani;
		$itemObj->DebtorAmount = 0;
		$itemObj->CreditorAmount = $PartObj->PartAmount + $WageSum;
		$itemObj->TafsiliType = TAFTYPE_PERSONS;
		$itemObj->TafsiliID = $ReqPersonTafsili;
		if(!$itemObj->Add($pdo))
		{
			ExceptionHandler::PushException("خطا در ایجاد ردیف سند");
			return false;
		}
	}
	//---------- ردیف های تضمین  ----------
	
	$dt = PdoDataAccess::runquery("select * from DMS_documents 
		join BaseInfo b on(InfoID=DocType AND TypeID=8)
		join ACC_DocItems on(SourceType=" . DOCTYPE_DOCUMENT . " AND SourceID=DocumentID)
		where b.param1=1 AND ObjectType='loan' AND ObjectID=?", array($ReqObj->RequestID));
	
	if(count($dt) == 0)
	{
	
		$dt = PdoDataAccess::runquery("
			SELECT DocumentID, ParamValue, InfoDesc as DocTypeDesc
				FROM DMS_DocParamValues
				join DMS_DocParams using(ParamID)
				join DMS_documents d using(DocumentID)
				join BaseInfo b on(InfoID=d.DocType AND TypeID=8)
			where b.param1=1 AND paramType='currencyfield' AND ObjectType='loan' AND ObjectID=?",
			array($ReqObj->RequestID), $pdo);

		$SumAmount = 0;
		foreach($dt as $row)
		{
			unset($itemObj->ItemID);
			unset($itemObj->Tafsili2Type);
			unset($itemObj->Tafsili2ID);
			$itemObj->CostID = $CostCode_guaranteeAmount;
			$itemObj->DebtorAmount = $row["ParamValue"];
			$itemObj->CreditorAmount = 0;
			$itemObj->TafsiliType = TAFTYPE_PERSONS;
			$itemObj->TafsiliID = $LoanPersonTafsili;
			$itemObj->SourceType = DOCTYPE_DOCUMENT;
			$itemObj->SourceID = $row["DocumentID"];
			$itemObj->details = $row["DocTypeDesc"];
			if(!$itemObj->Add($pdo))
			{
				ExceptionHandler::PushException("خطا در ایجاد ردیف سند");
				return false;
			}
			$SumAmount += $row["ParamValue"]*1;
		}

		if($SumAmount > 0)
		{
			unset($itemObj->ItemID);
			unset($itemObj->TafsiliType);
			unset($itemObj->TafsiliID);
			unset($itemObj->Tafsili2Type);
			unset($itemObj->Tafsili2ID);
			unset($itemObj->details);
			$itemObj->CostID = $CostCode_guaranteeAmount2;
			$itemObj->DebtorAmount = 0;
			$itemObj->CreditorAmount = $SumAmount;	
			if(!$itemObj->Add($pdo))
			{
				ExceptionHandler::PushException("خطا در ایجاد ردیف سند");
				return false;
			}

			unset($itemObj->ItemID);
			$itemObj->CostID = $CostCode_guaranteeCount;
			$itemObj->DebtorAmount = count($dt);
			$itemObj->CreditorAmount = 0;	
			if(!$itemObj->Add($pdo))
			{
				ExceptionHandler::PushException("خطا در ایجاد ردیف سند");
				return false;
			}

			unset($itemObj->ItemID);
			$itemObj->CostID = $CostCode_guaranteeCount2;
			$itemObj->DebtorAmount = 0;
			$itemObj->CreditorAmount = count($dt);
			if(!$itemObj->Add($pdo))
			{
				ExceptionHandler::PushException("خطا در ایجاد ردیف سند");
				return false;
			}
		}
	}
	//------ ایجاد چک ------
	$chequeObj = new ACC_DocChecks();
	$chequeObj->DocID = $obj->DocID;
	$chequeObj->CheckDate = $PartObj->PartDate;
	if($LoanMode == "Agent")
		$chequeObj->amount = $PartObj->PartAmount - $TotalDelay;
	else
		$chequeObj->amount = $PartObj->PartAmount - $TotalDelay - $WageSum;
	$chequeObj->TafsiliID = $LoanPersonTafsili;
	$chequeObj->description = " پرداخت " . $PartObj->PartDesc . " وام شماره " . $ReqObj->RequestID;
	$chequeObj->Add($pdo);
	
	//---------------------------------------------------------
	if(ExceptionHandler::GetExceptionCount() > 0)
		return false;
	
	return true;
}

function ReturnPayPartDoc($partobj, $pdo){
	
	/*@var $PartObj LON_ReqParts */
	
	$temp = PdoDataAccess::runquery("select DocID 
		from ACC_DocItems join ACC_docs using(DocID) 
		where SourceType=" . DOCTYPE_LOAN_PAYMENT . " AND SourceID=? AND SourceID2=?", 
		array($partobj->RequestID, $partobj->PartID), $pdo);
	
	if(count($temp) == 0)
		return true;
	
	PdoDataAccess::runquery("delete from ACC_DocChecks where DocID=?", array($temp[0][0]), $pdo);
	PdoDataAccess::runquery("delete from ACC_DocItems where DocID=?", array($temp[0][0]), $pdo);
	PdoDataAccess::runquery("delete from ACC_docs where DocID=?", array($temp[0][0]), $pdo);
	
	return ExceptionHandler::GetExceptionCount() == 0;	
}

function EndPartDoc($ReqObj, $PartObj, $PaidAmount, $installmentCount, $pdo){
		
	require_once '../../loan/request/request.data.php';
	
	/*@var $ReqObj LON_requests */
	/*@var $PartObj LON_ReqParts */
	
	//------------- get CostCodes --------------------
	$LoanObj = new LON_loans($ReqObj->LoanID);
	$CostCode_Loan = FindCostID("110" . "-" . $LoanObj->_BlockCode);
	$CostCode_wage = FindCostID("750" . "-" . $LoanObj->_BlockCode);
	$CostCode_deposite = FindCostID("210-01");
	$CostCode_bank = FindCostID("101");
	$CostCode_pardakhtani = FindCostID("200");
	$CostCode_guaranteeAmount = FindCostID("904-02");
	$CostCode_guaranteeCount = FindCostID("904-01");
	$CostCode_guaranteeAmount2 = FindCostID("905-02");
	$CostCode_guaranteeCount2 = FindCostID("905-01");
	//------------------------------------------------
	
	$CycleID = substr(DateModules::shNow(), 0 , 4);
	
	//---------------- add doc header --------------------
	$obj = new ACC_docs();
	$obj->RegDate = PDONOW;
	$obj->regPersonID = $_SESSION['USER']["PersonID"];
	$obj->DocDate = PDONOW;
	$obj->CycleID = $CycleID;
	$obj->BranchID = $ReqObj->BranchID;
	$obj->DocType = DOCTYPE_LOAN_PAYMENT;
	$obj->description = "اتمام " . $PartObj->PartDesc . " وام شماره " . $ReqObj->RequestID;
	
	if(!$obj->Add($pdo))
	{
		ExceptionHandler::PushException("خطا در ایجاد سند");
		return false;
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
	
	//--------------------- compute for new Amount ---------------------

	$new_TotalWage = round(ComputeWage($PaidAmount, $PartObj->CustomerWage/100, $installmentCount, $YearMonths));	
	$PartObj->InstallmentCount = $installmentCount;
	$new_year1 = $FundFactor*YearWageCompute($PartObj, $new_TotalWage, 1, $YearMonths);
	$new_year2 = $FundFactor*YearWageCompute($PartObj, $new_TotalWage, 2, $YearMonths);
	$new_year3 = $FundFactor*YearWageCompute($PartObj, $new_TotalWage, 3, $YearMonths);
	$new_year4 = $FundFactor*YearWageCompute($PartObj, $new_TotalWage, 4, $YearMonths);
	
	$new_TotalDelay = round($PaidAmount*$PartObj->CustomerWage*$PartObj->DelayMonths/1200);
	
	//------------------ find tafsilis ---------------
	$LoanPersonTafsili = FindTafsiliID($ReqObj->LoanPersonID, TAFTYPE_PERSONS);
	if(!$LoanPersonTafsili)
	{
		ExceptionHandler::PushException("تفصیلی مربوطه یافت نشد.[" . $ReqObj->LoanPersonID . "]");
		return false;
	}
	
	$LoanMode = "";
	$PersonObj = new BSC_persons($ReqObj->ReqPersonID);
	if($PersonObj->IsAgent == "YES")
		$LoanMode = "Agent";
	if($PersonObj->IsStaff == "YES" && $ReqObj->SupportPersonID > 0)
		$LoanMode = "Supporter";
	if($PersonObj->IsCustomer == "YES")
		$LoanMode = "Customer";
	
	if($LoanMode == "Agent")
	{
		$ReqPersonTafsili = FindTafsiliID($ReqObj->ReqPersonID, TAFTYPE_PERSONS);
		if(!$ReqPersonTafsili)
		{
			ExceptionHandler::PushException("تفصیلی مربوطه یافت نشد.[" . $ReqObj->ReqPersonID . "]");
			return false;
		}
	}
	if($LoanMode == "Supporter")
	{
		$SupporterTafsili = FindTafsiliID($ReqObj->SupportPersonID, TAFTYPE_PERSONS);
		if(!$SupporterTafsili)
		{
			ExceptionHandler::PushException("تفصیلی مربوطه یافت نشد.[" . $ReqObj->SupportPersonID . "]");
			return false;
		}
	}
	$amountArr = array();
	$curYearTafsili = FindTafsiliID($curYear, TAFTYPE_YEARS);
	if(!$curYear)
	{
		ExceptionHandler::PushException("تفصیلی مربوطه یافت نشد.[" . $curYear . "]");
		return false;
	}
	$amountArr[$curYearTafsili] = round($year1) - round($new_year1);
	
	if($year2 > 0)
	{
		$Year2Tafsili = FindTafsiliID($curYear+1, TAFTYPE_YEARS);
		if(!$Year2Tafsili)
		{
			ExceptionHandler::PushException("تفصیلی مربوطه یافت نشد.[" . ($curYear+1) . "]");
			return false;
		}
		$amountArr[$Year2Tafsili] = round($year2) - round($new_year2);
	}
	if($year3 > 0)
	{
		$Year3Tafsili = FindTafsiliID($curYear+2, TAFTYPE_YEARS);
		if(!$Year3Tafsili)
		{
			ExceptionHandler::PushException("تفصیلی مربوطه یافت نشد.[" . ($curYear+2) . "]");
			return false;
		}
		$amountArr[$Year3Tafsili] = round($year3) - round($new_year3);
	}
	if($year4 > 0)
	{
		$Year4Tafsili = FindTafsiliID($curYear+3, TAFTYPE_YEARS);
		if(!$Year4Tafsili)
		{
			ExceptionHandler::PushException("تفصیلی مربوطه یافت نشد.[" . ($curYear+3) . "]");
			return false;
		}
		$amountArr[$Year4Tafsili] = round($year4) - round($new_year4);
	}
	//----------------- add Doc items ------------------------
		
	$itemObj = new ACC_DocItems();
	$itemObj->DocID = $obj->DocID;
	$itemObj->CostID = $CostCode_Loan;
	$itemObj->DebtorAmount = 0;
	$itemObj->CreditorAmount = $PartObj->PartAmount - $PaidAmount;
	$itemObj->TafsiliType = TAFTYPE_PERSONS;
	$itemObj->TafsiliID = $LoanPersonTafsili;
	if($LoanMode == "Agent")
	{
		$itemObj->Tafsili2Type = TAFTYPE_PERSONS;
		$itemObj->Tafsili2ID = $ReqPersonTafsili;
	}
	if($LoanMode == "Supporter")
	{
		$itemObj->Tafsili2Type = TAFTYPE_PERSONS;
		$itemObj->Tafsili2ID = $SupporterTafsili;
	}
	$itemObj->locked = "YES";
	$itemObj->SourceType = DOCTYPE_LOAN_PAYMENT;
	$itemObj->SourceID = $ReqObj->RequestID;
	$itemObj->SourceID2 = $PartObj->PartID;
	if(!$itemObj->Add($pdo))
	{
		ExceptionHandler::PushException("خطا در ایجاد ردیف سند");
		return false;
	}
	
	//---- delay -----
	if($TotalDelay > 0)
	{
		unset($itemObj->ItemID);
		unset($itemObj->Tafsili2Type);
		unset($itemObj->Tafsili2ID);
		$itemObj->CostID = $CostCode_wage;
		$itemObj->DebtorAmount = $TotalDelay - $new_TotalDelay;
		$itemObj->CreditorAmount = 0;
		$itemObj->TafsiliType = TAFTYPE_YEARS;
		$itemObj->details = "تفاوت کارمزد دوره تنفس";
		$itemObj->TafsiliID = $curYearTafsili;
		if(!$itemObj->Add($pdo))
		{
			ExceptionHandler::PushException("خطا در ایجاد ردیف سند");
			return false;
		}
	}	
	//---- کارمزد-----
	$WageSum = 0;
	foreach($amountArr as $yearTafsili => $amount)
	{
		if($amount == 0 || $amount == "")
			continue;
		
		unset($itemObj->ItemID);
		unset($itemObj->Tafsili2Type);
		unset($itemObj->Tafsili2ID);
		$itemObj->CostID = $CostCode_wage;
		$itemObj->DebtorAmount = $amount;
		$itemObj->CreditorAmount = 0;
		$itemObj->TafsiliType = TAFTYPE_YEARS;
		$itemObj->TafsiliID = $yearTafsili;
		$itemObj->details = "تفاوت کارمزد";
		if(!$itemObj->Add($pdo))
		{
			ExceptionHandler::PushException("خطا در ایجاد ردیف سند");
			return false;
		}
		
		if($LoanMode == "Agent")
		{
			unset($itemObj->ItemID);
			unset($itemObj->Tafsili2Type);
			unset($itemObj->Tafsili2ID);
			$itemObj->CostID = $CostCode_deposite;
			$itemObj->DebtorAmount = 0;
			$itemObj->CreditorAmount = $amount;
			$itemObj->TafsiliType = TAFTYPE_PERSONS;
			$itemObj->TafsiliID = $ReqPersonTafsili;
			if(!$itemObj->Add($pdo))
			{
				ExceptionHandler::PushException("خطا در ایجاد ردیف سند");
				return false;
			}
		}
		else
			$WageSum += $amount;
	}
	// ---- bank ----
	$itemObj = new ACC_DocItems();
	$itemObj->DocID = $obj->DocID;
	$itemObj->CostID = $CostCode_bank;
	if($LoanMode == "Agent")
		$itemObj->DebtorAmount = $PartObj->PartAmount - $TotalDelay - $WageSum - ($PaidAmount - $new_TotalDelay);
	else
		$itemObj->DebtorAmount = $PartObj->PartAmount - $TotalDelay - ($PaidAmount - $new_TotalDelay);
	$itemObj->CreditorAmount = 0;
	$itemObj->TafsiliType = TAFTYPE_BANKS;
	$itemObj->locked = "YES";
	$itemObj->SourceType = DOCTYPE_LOAN_PAYMENT;
	$itemObj->SourceID = $PartObj->PartID;
	if(!$itemObj->Add($pdo))
	{
		ExceptionHandler::PushException("خطا در ایجاد ردیف سند");
		return false;
	}
	
	if($LoanMode == "Agent")
	{
		//---- کسر از سپرده -----
		unset($itemObj->ItemID);
		unset($itemObj->Tafsili2Type);
		unset($itemObj->Tafsili2ID);
		$itemObj->CostID = $CostCode_deposite;
		$itemObj->DebtorAmount = 0;
		$itemObj->CreditorAmount = $PartObj->PartAmount + $WageSum - $PaidAmount ;
		$itemObj->TafsiliType = TAFTYPE_PERSONS;
		$itemObj->TafsiliID = $ReqPersonTafsili;
		if(!$itemObj->Add($pdo))
		{
			ExceptionHandler::PushException("خطا در ایجاد ردیف سند");
			return false;
		}

		unset($itemObj->ItemID);
		unset($itemObj->Tafsili2Type);
		unset($itemObj->Tafsili2ID);
		$itemObj->CostID = $CostCode_pardakhtani;
		$itemObj->DebtorAmount = $PartObj->PartAmount + $WageSum - $PaidAmount;
		$itemObj->CreditorAmount = 0;
		$itemObj->TafsiliType = TAFTYPE_PERSONS;
		$itemObj->TafsiliID = $ReqPersonTafsili;
		if(!$itemObj->Add($pdo))
		{
			ExceptionHandler::PushException("خطا در ایجاد ردیف سند");
			return false;
		}
	}
	
	//---------------------------------------------------------
	if(ExceptionHandler::GetExceptionCount() > 0)
		return false;
	
	return true;
}

function RegisterPayInstallmentDoc($InstallmentObj, $pdo){
	
	/*@var $InstallmentObj LON_installments */
	
	$CycleID = substr(DateModules::shNow(), 0 , 4);
	
	$PartObj = new LON_ReqParts($InstallmentObj->PartID);
	$ReqObj = new LON_requests($PartObj->RequestID);
	
	//------------- get CostCodes --------------------
	$LoanObj = new LON_loans($ReqObj->LoanID);
	$CostCode_Loan = FindCostID("110" . "-" . $LoanObj->_BlockCode);
	$CostCode_deposite = FindCostID("210-01");
	$CostCode_bank = FindCostID("101");
	$CostCode_pardakhtani = FindCostID("200");
	
	//---------------- add doc header --------------------
	$obj = new ACC_docs();
	$obj->RegDate = PDONOW;
	$obj->regPersonID = $_SESSION['USER']["PersonID"];
	$obj->DocDate = PDONOW;
	$obj->CycleID = $CycleID;
	$obj->BranchID = $ReqObj->BranchID;
	$obj->DocType = DOCTYPE_INSTALLMENT_PAYMENT;
	$obj->description = "پرداخت قسط " . $InstallmentObj->InstallmentDate . " " . 
			$PartObj->PartDesc . " وام شماره " . $ReqObj->RequestID;
	
	if(!$obj->Add($pdo))
		return false;
	
	//------------------ find tafsilis ---------------
	$LoanPersonTafsili = FindTafsiliID($ReqObj->LoanPersonID, TAFTYPE_PERSONS);
	if(!$LoanPersonTafsili)
	{
		ExceptionHandler::PushException("تفصیلی مربوطه یافت نشد.[" . $ReqObj->LoanPersonID . "]");
		return false;
	}
	
	$LoanMode = "";
	$PersonObj = new BSC_persons($ReqObj->ReqPersonID);
	if($PersonObj->IsAgent == "YES")
		$LoanMode = "Agent";
	if($PersonObj->IsStaff == "YES" && $ReqObj->SupportPersonID > 0)
		$LoanMode = "Supporter";
	if($PersonObj->IsCustomer == "YES")
		$LoanMode = "Customer";
	
	if($LoanMode == "Agent")
	{
		$ReqPersonTafsili = FindTafsiliID($ReqObj->ReqPersonID, TAFTYPE_PERSONS);
		if(!$ReqPersonTafsili)
		{
			ExceptionHandler::PushException("تفصیلی مربوطه یافت نشد.[" . $ReqObj->ReqPersonID . "]");
			return false;
		}
	}
	//----------------- add Doc items ------------------------
		
	$itemObj = new ACC_DocItems();
	$itemObj->DocID = $obj->DocID;
	$itemObj->CostID = $CostCode_Loan;
	$itemObj->CreditorAmount = $InstallmentObj->PaidAmount;
	$itemObj->DebtorAmount = 0;
	$itemObj->TafsiliType = TAFTYPE_PERSONS;
	$itemObj->TafsiliID = $LoanPersonTafsili;
	if($LoanMode == "Agent")
	{
		$itemObj->Tafsili2Type = TAFTYPE_PERSONS;
		$itemObj->Tafsili2ID = $ReqPersonTafsili;
	}
	if($LoanMode == "Supporter")
	{
		$itemObj->Tafsili2Type = TAFTYPE_PERSONS;
		$itemObj->Tafsili2ID = $SupporterTafsili;
	}
	$itemObj->locked = "YES";
	$itemObj->SourceType = DOCTYPE_INSTALLMENT_PAYMENT;
	$itemObj->SourceID = $ReqObj->RequestID;
	$itemObj->SourceID2 = $InstallmentObj->InstallmentID;
	if(!$itemObj->Add($pdo))
		return false;
	
	// ---- bank ----
	$itemObj = new ACC_DocItems();
	$itemObj->DocID = $obj->DocID;
	$itemObj->CostID = $CostCode_bank;
	$itemObj->DebtorAmount= $InstallmentObj->PaidAmount;
	$itemObj->CreditorAmount = 0;
	$itemObj->TafsiliType = TAFTYPE_BANKS;
	$itemObj->locked = "YES";
	$itemObj->SourceType = DOCTYPE_LOAN_PAYMENT;
	$itemObj->SourceID = $PartObj->PartID;
	if(!$itemObj->Add($pdo))
		return false;
	
	if($LoanMode == "Agent")
	{
		//---- اضافه به سپرده -----
		unset($itemObj->ItemID);
		unset($itemObj->Tafsili2Type);
		unset($itemObj->Tafsili2ID);
		$itemObj->CostID = $CostCode_deposite;
		$itemObj->DebtorAmount = 0;
		$itemObj->CreditorAmount = $InstallmentObj->PaidAmount;
		$itemObj->TafsiliType = TAFTYPE_PERSONS;
		$itemObj->TafsiliID = $ReqPersonTafsili;
		if(!$itemObj->Add($pdo))
			return false;

		unset($itemObj->ItemID);
		unset($itemObj->Tafsili2Type);
		unset($itemObj->Tafsili2ID);
		$itemObj->CostID = $CostCode_pardakhtani;
		$itemObj->DebtorAmount = $InstallmentObj->PaidAmount;
		$itemObj->CreditorAmount = 0;
		$itemObj->TafsiliType = TAFTYPE_PERSONS;
		$itemObj->TafsiliID = $ReqPersonTafsili;
		if(!$itemObj->Add($pdo))
			return false;
	}
	
	//---------------------------------------------------------
	if(ExceptionHandler::GetExceptionCount() > 0)
		return false;
		
	return true;
}

//---------------------------------------------------------------

function ComputeDepositeProfit(){
	
	//-------------- get latest deposite compute -------------
	$FirstYearDay = DateModules::shamsi_to_miladi($_SESSION["accounting"]["CycleID"] . "-01-01", "-");
	$dt = PdoDataAccess::runquery("select DocID,DocDate 
		from ACC_docs where DocType=" . DOCTYPE_DEPOSIT_PROFIT . " 
			AND CycleID=" . $_SESSION["accounting"]["CycleID"] . "
			AND BranchID=" . $_SESSION["accounting"]["BranchID"] . "	
		order by DocID desc");
	
	$LatestComputeDate = count($dt)==0 ? $FirstYearDay : $dt[0]["DocDate"];
	$LatestComputeDocID = count($dt)==0 ? 0 : $dt[0]["DocID"];
	
	//----------- check for all docs confirm --------------
	$dt = PdoDataAccess::runquery("select group_concat(distinct LocalNo) from ACC_docs 
		join ACC_DocItems using(DocID)
		where DocID>=? AND CostID in(" . ShortDepositeCostID . "," . LongDepositeCostID . ")
		AND DocStatus not in('CONFIRM','ARCHIVE')", array($LatestComputeDocID));
	if(count($dt) > 0 && $dt[0][0] != "")
	{
		echo Response::createObjectiveResponse(false, "اسناد با شماره های [" . $dt[0][0] . "] تایید نشده اند و قادر به صدور سند سود سپرده نمی باشید.");
		die();
	}
	
	//--------------get percents -----------------
	$dt = PdoDataAccess::runquery("select * from ACC_cycles where CycleID=" . 
			$_SESSION["accounting"]["CycleID"]);
	$DepositePercents = array(
		ShortDepositeCostID => $dt[0]["ShortDepositPercent"],
		LongDepositeCostID  => $dt[0]["LongDepositPercent"]
	);
	
	//------------ get sum of deposites ----------------
	$dt = PdoDataAccess::runquery("select TafsiliID,CostID,sum(CreditorAmount-DebtorAmount) amount
		from ACC_DocItems join ACC_docs using(DocID)
		where DocID<=? 
			AND CostID in(" . ShortDepositeCostID . "," . LongDepositeCostID . ")
			AND CycleID=" . $_SESSION["accounting"]["CycleID"] . "
			AND BranchID=" . $_SESSION["accounting"]["BranchID"] . "
		group by TafsiliID,CostID", 
		array($LatestComputeDocID));
	$DepositeAmount = array(
		ShortDepositeCostID => array(),
		LongDepositeCostID => array()
	);
	
	foreach($dt as $row)
	{
		$DepositeAmount[ $row["CostID"] ][ $row["TafsiliID"] ]["amount"] = $row["amount"];
		$DepositeAmount[ $row["CostID"] ][ $row["TafsiliID"] ]["lastDate"] = $LatestComputeDate;
	}
	//------------ get the Deposite amount -------------
	$dt = PdoDataAccess::runquery("
		select CostID,TafsiliID,DocDate,CreditorAmount-DebtorAmount amount
		from ACC_DocItems 
			join ACC_docs using(DocID)
		where CostID in(" . ShortDepositeCostID . "," . LongDepositeCostID . ")
			AND DocID > ?
			AND CycleID=" . $_SESSION["accounting"]["CycleID"] . "
			AND BranchID=" . $_SESSION["accounting"]["BranchID"], array($LatestComputeDocID));
	
	foreach($dt as $row)
	{
		if(!isset($DepositeAmount[ $row["CostID"] ][ $row["TafsiliID"] ]["lastDate"]))
		{
			$DepositeAmount[ $row["CostID"] ][ $row["TafsiliID"] ]["lastDate"] = $FirstYearDay;
			$DepositeAmount[ $row["CostID"] ][ $row["TafsiliID"] ]["amount"] = 0;
		}
		$lastDate = $DepositeAmount[ $row["CostID"] ][ $row["TafsiliID"] ]["lastDate"];
		$days = DateModules::GDateMinusGDate($row["DocDate"], $lastDate);
		
		$amount = $DepositeAmount[ $row["CostID"] ][ $row["TafsiliID"] ]["amount"] * $days * 
			$DepositePercents[ $row["CostID"] ]/(100*30.5);
		
		if(!isset($DepositeAmount[ $row["CostID"] ][ $row["TafsiliID"] ]["profit"]))
			$DepositeAmount[ $row["CostID"] ][ $row["TafsiliID"] ]["profit"] = 0;
		$DepositeAmount[ $row["CostID"] ][ $row["TafsiliID"] ]["profit"] += $amount;
		
		//echo $row["TafsiliID"] ."@" . $DepositeAmount[ $row["CostID"] ][ $row["TafsiliID"] ]["profit"] . "\n";
		
		$DepositeAmount[ $row["CostID"] ][ $row["TafsiliID"] ]["amount"] += $row["amount"];
		$DepositeAmount[ $row["CostID"] ][ $row["TafsiliID"] ]["lastDate"] = $row["DocDate"];	
	}
	
	foreach($DepositeAmount[ ShortDepositeCostID ] as $tafsili => &$row)
	{
		$days = DateModules::GDateMinusGDate(DateModules::Now(), $row["lastDate"]);
		$amount = $row["amount"] * $days * $DepositePercents[ ShortDepositeCostID ]/(100*30.5);

		if(!isset($row["profit"]))
			$row["profit"] = 0;
		$row["profit"] += $amount;
		
		//echo $tafsili ."@" . $DepositeAmount[ ShortDepositeCostID ][ $tafsili ]["profit"] . "\n";
	}
	foreach($DepositeAmount[ LongDepositeCostID ] as $tafsili => &$row)
	{
		$days = DateModules::GDateMinusGDate(DateModules::Now(), $row["lastDate"]);
		$amount = $row["amount"] * $days * $DepositePercents[ LongDepositeCostID ]/(100*30.5);

		if(!isset($row["profit"]))
			$row["profit"] = 0;
		$row["profit"] += $amount;
		
		//echo $tafsili ."@" . $DepositeAmount[ ShortDepositeCostID ][ $tafsili ]["profit"] . "\n";
	}
	
	$pdo = PdoDataAccess::getPdoObject();
	$pdo->beginTransaction();
	
	//--------------- add doc header ------------------
	$obj = new ACC_docs();
	$obj->RegDate = PDONOW;
	$obj->regPersonID = $_SESSION['USER']["PersonID"];
	$obj->DocDate = PDONOW;
	$obj->CycleID = $_SESSION["accounting"]["CycleID"];
	$obj->BranchID = $_SESSION["accounting"]["BranchID"];
	$obj->DocType = DOCTYPE_DEPOSIT_PROFIT;
	$obj->description = "محاسبه سود سپرده";
	
	if(!$obj->Add($pdo))
	{
		echo Response::createObjectiveResponse(false, "خطا در ایجاد سند");
		die();
	}
	
	//---------------- add DocItems -------------------
	$sumAmount = 0;
	foreach($DepositeAmount as $CostID => $DepRow)
	{
		foreach($DepRow as $TafsiliID => $itemrow)
		{
			$itemObj = new ACC_DocItems();
			$itemObj->DocID = $obj->DocID;
			$itemObj->CostID = $CostID;
			$itemObj->CreditorAmount = round($itemrow["profit"]>0 ? $itemrow["profit"] : 0);
			$itemObj->DebtorAmount = round($itemrow["profit"]<0 ? -1*$itemrow["profit"] : 0);
			$itemObj->TafsiliType = TAFTYPE_PERSONS;
			$itemObj->TafsiliID = $TafsiliID;
			$itemObj->locked = "YES";
			$itemObj->SourceType = DOCTYPE_DEPOSIT_PROFIT;
			if(!$itemObj->Add($pdo))
			{
				echo Response::createObjectiveResponse(false, "خطا در ایجاد ردیف سند");
				die();
			}
			
			$sumAmount += round($itemrow["profit"]);
		}
	}
	//---------------------- add fund row ----------------
	
	$itemObj = new ACC_DocItems();
	$itemObj->DocID = $obj->DocID;
	$itemObj->CostID = FundCostID;
	$itemObj->DebtorAmount= $sumAmount;
	$itemObj->CreditorAmount = 0;
	$itemObj->locked = "YES";
	$itemObj->SourceType = DOCTYPE_DEPOSIT_PROFIT;
	if(!$itemObj->Add($pdo))
	{
		return false;
	}
	
	
	$pdo->commit();
	echo Response::createObjectiveResponse(true, "");
	die();	
}

?>