<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 94.06
//-----------------------------

require_once '../header.inc.php';
require_once getenv("DOCUMENT_ROOT") . '/accounting/definitions.inc.php';
require_once getenv("DOCUMENT_ROOT") . '/framework/person/persons.class.php';
require_once getenv("DOCUMENT_ROOT") . '/loan/loan/loan.class.php';
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

function RegisterPayPartDoc($ReqObj, $PartObj, $PayAmount, $pdo){
		
	require_once '../../loan/request/request.data.php';
	
	/*@var $ReqObj LON_requests */
	/*@var $PartObj LON_ReqParts */
	
	$PartObj->MaxFundWage = $PartObj->MaxFundWage*1;
	
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
	$CostCode_FutureWage = FindCostID("760" . "-" . $LoanObj->_BlockCode);
	$CostCode_deposite = FindCostID("210-01");
	$CostCode_bank = FindCostID("101");
	$CostCode_commitment = FindCostID("200-05");
	$CostCode_guaranteeAmount = FindCostID("904-02");
	$CostCode_guaranteeCount = FindCostID("904-01");
	$CostCode_guaranteeAmount2 = FindCostID("905-02");
	$CostCode_guaranteeCount2 = FindCostID("905-01");
	$CostCode_todiee = FindCostID("200-03");
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
	$obj->description = "پرداخت " . $PartObj->PartDesc . " وام شماره " . $ReqObj->RequestID . " به نام " . 
		$ReqObj->_LoanPersonFullname;
	
	if(!$obj->Add($pdo))
	{
		ExceptionHandler::PushException("خطا در ایجاد سند");
		return false;
	}
	
	//--------------------------------------------------------
	$MaxWage = max($PartObj->CustomerWage*1 , $PartObj->FundWage);
	$YearMonths = ($PartObj->IntervalType == "DAY") ? floor(365/$PartObj->PayInterval) : 12;
	$TotalWage = round(ComputeWage($PayAmount, $MaxWage/100, $PartObj->InstallmentCount, 
		$YearMonths, $PartObj->PayInterval));	
	$FundFactor = $MaxWage == 0 ? 0 : $PartObj->FundWage/$MaxWage;
	
	if($PartObj->MaxFundWage*1 > 0 && $PartObj->WageReturn == "INSTALLMENT")
	{
		$year1 = $FundFactor*YearWageCompute($PartObj, $PartObj->MaxFundWage*1, 1, $YearMonths);
		$year2 = $FundFactor*YearWageCompute($PartObj, $PartObj->MaxFundWage*1, 2, $YearMonths);
		$year3 = $FundFactor*YearWageCompute($PartObj, $PartObj->MaxFundWage*1, 3, $YearMonths);
		$year4 = $FundFactor*YearWageCompute($PartObj, $PartObj->MaxFundWage*1, 4, $YearMonths);
	}
	else
	{	
		$year1 = $FundFactor*YearWageCompute($PartObj, $TotalWage, 1, $YearMonths);
		$year2 = $FundFactor*YearWageCompute($PartObj, $TotalWage, 2, $YearMonths);
		$year3 = $FundFactor*YearWageCompute($PartObj, $TotalWage, 3, $YearMonths);
		$year4 = $FundFactor*YearWageCompute($PartObj, $TotalWage, 4, $YearMonths);
	}
	$TotalDelay = round($PayAmount*$MaxWage*$PartObj->DelayMonths/1200);
	$curYear = substr(DateModules::miladi_to_shamsi($PartObj->PartDate), 0, 4)*1;
	
	//------------------ find tafsilis ---------------
	$LoanPersonTafsili = FindTafsiliID($ReqObj->LoanPersonID, TAFTYPE_PERSONS);
	if(!$LoanPersonTafsili)
	{
		ExceptionHandler::PushException("تفصیلی مربوطه یافت نشد.[" . $ReqObj->LoanPersonID . "]");
		return false;
	}
	
	$LoanMode = "";
	if(!empty($ReqObj->ReqPersonID))
	{
		$PersonObj = new BSC_persons($ReqObj->ReqPersonID);
		if($PersonObj->IsAgent == "YES")
			$LoanMode = "Agent";
	}
	else
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
	$amountArr = array();
	$curYearTafsili = FindTafsiliID($curYear, TAFTYPE_YEARS);
	if(!$curYear)
	{
		ExceptionHandler::PushException("تفصیلی مربوطه یافت نشد.[" . $curYear . "]");
		return false;
	}
	$amountArr[$curYearTafsili]["year"] = $curYear;
	$amountArr[$curYearTafsili]["amount"] = $year1;
	
	if($year2 > 0)
	{
		$Year2Tafsili = FindTafsiliID($curYear+1, TAFTYPE_YEARS);
		if(!$Year2Tafsili)
		{
			ExceptionHandler::PushException("تفصیلی مربوطه یافت نشد.[" . ($curYear+1) . "]");
			return false;
		}
		$amountArr[$Year2Tafsili]["year"] = $curYear+1;
		$amountArr[$Year2Tafsili]["amount"] = $year2;
	}
	if($year3 > 0)
	{
		$Year3Tafsili = FindTafsiliID($curYear+2, TAFTYPE_YEARS);
		if(!$Year3Tafsili)
		{
			ExceptionHandler::PushException("تفصیلی مربوطه یافت نشد.[" . ($curYear+2) . "]");
			return false;
		}		
		$amountArr[$Year3Tafsili]["year"] = $curYear+2;
		$amountArr[$Year3Tafsili]["amount"] = $year3;
	}
	if($year4 > 0)
	{
		$Year4Tafsili = FindTafsiliID($curYear+3, TAFTYPE_YEARS);
		if(!$Year4Tafsili)
		{
			ExceptionHandler::PushException("تفصیلی مربوطه یافت نشد.[" . ($curYear+3) . "]");
			return false;
		}
		$amountArr[$Year4Tafsili]["year"] = $curYear+3;
		$amountArr[$Year4Tafsili]["amount"] = $year4;
	}
	$WageSum = 0;
	foreach($amountArr as $row)
		$WageSum += round($row["amount"]);
	//------------ find the number step to pay ---------------
	$FirstStep = true;
	$dt = PdoDataAccess::runquery("select * from ACC_DocItems where SourceID=?", array($ReqObj->RequestID));
	if(count($dt) > 0)
	{
		$FirstStep = false;
		$query = "select ifnull(sum(CreditorAmount-DebtorAmount),0)
			from ACC_DocItems where CostID=? AND TafsiliID=? AND sourceID=?";
		$param = array($CostCode_todiee, $LoanPersonTafsili, $ReqObj->RequestID);
		if($LoanMode == "Agent")
		{
			$query .= " AND TafsiliID2=?";
			$param[] = $ReqPersonTafsili;
		}
		$dt = PdoDataAccess::runquery($query, $param);
		if($dt[0][0]*1 < $PayAmount)
		{
			ExceptionHandler::PushException("حساب تودیعی این مشتری"
					. number_format($dt[0][0]) . " ریال می باشد که کمتر از مبلغ این مرحله از وام می باشد");
			return false;
		}
	}
	//----------------- add Doc items ------------------------
	$itemObj = new ACC_DocItems();
	$itemObj->DocID = $obj->DocID;
	$itemObj->TafsiliType = TAFTYPE_PERSONS;
	$itemObj->TafsiliID = $LoanPersonTafsili;
	if($LoanMode == "Agent")
	{
		$itemObj->TafsiliType2 = TAFTYPE_PERSONS;
		$itemObj->TafsiliID2 = $ReqPersonTafsili;
	}
	/*if($LoanMode == "Supporter")
	{
		$itemObj->TafsiliType2 = TAFTYPE_PERSONS;
		$itemObj->TafsiliID2 = $SupporterTafsili;
	}*/
	$itemObj->locked = "YES";
	$itemObj->SourceType = DOCTYPE_LOAN_PAYMENT;
	$itemObj->SourceID = $ReqObj->RequestID;
	$itemObj->SourceID2 = $PartObj->PartID;
	
	if($FirstStep)
	{
		$amount = $PartObj->PartAmount;
		if($PartObj->WageReturn == "INSTALLMENT")
		{
			if($PartObj->MaxFundWage*1 > 0)
				$amount += $WageSum + $PartObj->MaxFundWage*1;
			else
				$amount += $WageSum*$PartObj->CustomerWage/$MaxWage;
		}  			
		
		unset($itemObj->ItemID);
		$itemObj->DocID = $obj->DocID;
		$itemObj->CostID = $CostCode_Loan;
		$itemObj->DebtorAmount = $amount;
		$itemObj->CreditorAmount = 0;
		$itemObj->Add($pdo);
		
		if($PartObj->PartAmount != $PayAmount)
		{
			unset($itemObj->ItemID);
			$itemObj->DocID = $obj->DocID;
			$itemObj->CostID = $CostCode_todiee;
			$itemObj->DebtorAmount = 0;
			$itemObj->CreditorAmount = $PartObj->PartAmount*1 - $PayAmount;
			$itemObj->Add($pdo);
		}
	}
	else
	{
		if($PartObj->WageReturn == "INSTALLMENT")
		{
			unset($itemObj->ItemID);
			$itemObj->DocID = $obj->DocID;
			$itemObj->CostID = $CostCode_Loan;
			if($PartObj->MaxFundWage*1 > 0)
				$itemObj->DebtorAmount = $WageSum + $PartObj->MaxFundWage*1;
			else
				$itemObj->DebtorAmount = $WageSum*$PartObj->CustomerWage/$MaxWage;
			$itemObj->CreditorAmount = 0;
			$itemObj->Add($pdo);
		}
		
		unset($itemObj->ItemID);
		$itemObj->DocID = $obj->DocID;
		$itemObj->CostID = $CostCode_todiee;
		$itemObj->DebtorAmount = $PayAmount;
		$itemObj->CreditorAmount = 0;
		$itemObj->Add($pdo);
	}
	//---- delay -----
	if($TotalDelay > 0 && $PartObj->MaxFundWage*1 == 0)
	{
		unset($itemObj->ItemID);
		unset($itemObj->TafsiliType2);
		unset($itemObj->TafsiliID2);
		$itemObj->CostID = $CostCode_wage;
		$itemObj->DebtorAmount = 0;
		$itemObj->CreditorAmount = $TotalDelay*$PartObj->FundWage/$MaxWage;
		$itemObj->TafsiliType = TAFTYPE_YEARS;
		$itemObj->details = "کارمزد دوره تنفس";
		$itemObj->TafsiliID = $curYearTafsili;
		$itemObj->Add($pdo);
		
		if($PartObj->WageReturn != "AGENT" && $PartObj->FundWage*1 > $PartObj->CustomerWage)
		{
			unset($itemObj->ItemID);
			$itemObj->CostID = $CostCode_deposite;
			$itemObj->DebtorAmount = $TotalDelay - $TotalDelay*$PartObj->CustomerWage/$MaxWage;
			$itemObj->CreditorAmount = 0;
			$itemObj->details = "بابت اختلاف کارمزد تنفس " . $PartObj->PartDesc . " وام شماره " . $ReqObj->RequestID;
			$itemObj->TafsiliType = TAFTYPE_PERSONS;
			$itemObj->TafsiliID = $ReqPersonTafsili;
			$itemObj->Add($pdo);
		}
		
		if($PartObj->FundWage*1 < $PartObj->CustomerWage*1)
		{
			unset($itemObj->ItemID);
			$itemObj->CostID = $CostCode_deposite;
			$itemObj->DebtorAmount = 0;
			$itemObj->CreditorAmount = $TotalDelay - $TotalDelay*$PartObj->FundWage/$MaxWage;
			$itemObj->TafsiliType = TAFTYPE_PERSONS;
			$itemObj->details = "بابت سهم کارمزد تنفس " . $PartObj->PartDesc . " وام شماره " . $ReqObj->RequestID;
			$itemObj->TafsiliID = $ReqPersonTafsili;
			$itemObj->Add($pdo);
		}
	}	
	//------------------------ کارمزد---------------------
	
	foreach($amountArr as $yearTafsili => $arr)
	{
		if($arr["amount"] == 0 || $arr["amount"] == "")
			continue;
		
		unset($itemObj->ItemID);
		$itemObj->details = "";
		unset($itemObj->TafsiliType2);
		unset($itemObj->TafsiliID2);
		$itemObj->CostID = $arr["year"] == $curYear ? $CostCode_wage : $CostCode_FutureWage;
		$itemObj->DebtorAmount = 0;
		if($PartObj->MaxFundWage > 0)
			$itemObj->CreditorAmount = round($arr["amount"]);
		else
			$itemObj->CreditorAmount = round($arr["amount"]*$PartObj->FundWage/$MaxWage);
		$itemObj->TafsiliType = TAFTYPE_YEARS;
		$itemObj->TafsiliID = $yearTafsili;
		$itemObj->Add($pdo);
		
		if($LoanMode == "Agent" && $PartObj->WageReturn == "AGENT")
		{
			unset($itemObj->ItemID);
			unset($itemObj->TafsiliType2);
			unset($itemObj->TafsiliID2);
			$itemObj->CostID = $CostCode_deposite;
			$itemObj->DebtorAmount = round($arr["amount"]*$PartObj->FundWage/$MaxWage);
			$itemObj->CreditorAmount = 0;
			$itemObj->details = "بابت کارمزد سال " . $arr["year"] . 
				$PartObj->PartDesc . " وام شماره " . $ReqObj->RequestID; 
			$itemObj->TafsiliType = TAFTYPE_PERSONS;
			$itemObj->TafsiliID = $ReqPersonTafsili;
			$itemObj->Add($pdo);
		}
	}
	
	if($PartObj->MaxFundWage > 0)
	{
		unset($itemObj->ItemID);
		$itemObj->details = "";
		unset($itemObj->TafsiliType2);
		unset($itemObj->TafsiliID2);
		$itemObj->CostID = $CostCode_wage;
		$itemObj->DebtorAmount = 0;
		$itemObj->CreditorAmount = $PartObj->MaxFundWage;
		$itemObj->TafsiliType = TAFTYPE_YEARS;
		$itemObj->TafsiliID = $yearTafsili;
		$itemObj->Add($pdo);
	}
	
	if($PartObj->WageReturn != "AGENT" && $PartObj->FundWage*1 > $PartObj->CustomerWage)
	{
		if($WageSum - $WageSum*$PartObj->CustomerWage/$MaxWage > 0)
		{
			unset($itemObj->ItemID);
			$itemObj->CostID = $CostCode_deposite;
			$itemObj->DebtorAmount = $WageSum - $WageSum*$PartObj->CustomerWage/$MaxWage;
			$itemObj->CreditorAmount = 0;
			$itemObj->details = "بابت اختلاف کارمزد " . $PartObj->PartDesc . " وام شماره " . $ReqObj->RequestID;
			$itemObj->TafsiliType = TAFTYPE_PERSONS;
			$itemObj->TafsiliID = $ReqPersonTafsili;
			$itemObj->Add($pdo);
		}
	}
	if($PartObj->WageReturn != "AGENT" && $PartObj->FundWage*1 < $PartObj->CustomerWage)
	{
		if($WageSum - $WageSum*$PartObj->FundWage/$MaxWage > 0)
		{
			unset($itemObj->ItemID);
			$itemObj->CostID = $CostCode_deposite;
			$itemObj->DebtorAmount = 0;
			$itemObj->CreditorAmount = $WageSum - $WageSum*$PartObj->FundWage/$MaxWage;
			$itemObj->details = "بابت سهم کارمزد " . $PartObj->PartDesc . " وام شماره " . $ReqObj->RequestID;
			$itemObj->TafsiliType = TAFTYPE_PERSONS;
			$itemObj->TafsiliID = $ReqPersonTafsili;
			$itemObj->Add($pdo);
		}
	}
	// ----------------------------- bank --------------------------------
	$itemObj = new ACC_DocItems();
	$itemObj->DocID = $obj->DocID;
	$itemObj->CostID = $CostCode_bank;
	$itemObj->DebtorAmount = 0;
	if($PartObj->MaxFundWage > 0)
		$BankItemAmount = $PayAmount - 
			($PartObj->WageReturn == "CUSTOMER" ? $PartObj->MaxFundWage + $WageSum : 0);
	else
		$BankItemAmount = $PayAmount - $TotalDelay*$PartObj->CustomerWage/$MaxWage - 
			($PartObj->WageReturn == "CUSTOMER" ? $WageSum*$PartObj->CustomerWage/$MaxWage : 0);
	$itemObj->CreditorAmount = $BankItemAmount;
	$itemObj->TafsiliType = TAFTYPE_BANKS;
	$itemObj->locked = "YES";
	$itemObj->SourceType = DOCTYPE_LOAN_PAYMENT;
	$itemObj->SourceID = $ReqObj->RequestID;
	$itemObj->SourceID2 = $PartObj->PartID;
	$itemObj->Add($pdo);
	
	if($LoanMode == "Agent")
	{
		//---- کسر از سپرده -----
		unset($itemObj->ItemID);
		unset($itemObj->TafsiliType2);
		unset($itemObj->TafsiliID2);
		$itemObj->CostID = $CostCode_deposite;
		$itemObj->DebtorAmount = $PayAmount;
		$itemObj->CreditorAmount = 0;
		$itemObj->details = "بابت پرداخت " . $PartObj->PartDesc . " وام شماره " . $ReqObj->RequestID;
		$itemObj->TafsiliType = TAFTYPE_PERSONS;
		$itemObj->TafsiliID = $ReqPersonTafsili;
		$itemObj->Add($pdo);

		unset($itemObj->ItemID);
		unset($itemObj->TafsiliType2);
		unset($itemObj->TafsiliID2);
		$itemObj->CostID = $CostCode_commitment;
		$itemObj->DebtorAmount = 0;
		$itemObj->CreditorAmount = $PayAmount;
		$itemObj->TafsiliType = TAFTYPE_PERSONS;
		$itemObj->TafsiliID = $LoanPersonTafsili;		
		$itemObj->TafsiliType2 = TAFTYPE_PERSONS;
		$itemObj->TafsiliID2 = $ReqPersonTafsili;
		$itemObj->Add($pdo);
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
		$countAmount = 0;
		foreach($dt as $row)
		{
			unset($itemObj->ItemID);
			unset($itemObj->TafsiliType2);
			unset($itemObj->TafsiliID2);
			$itemObj->CostID = $CostCode_guaranteeAmount;
			$itemObj->DebtorAmount = $row["ParamValue"];
			$itemObj->CreditorAmount = 0;
			$itemObj->TafsiliType = TAFTYPE_PERSONS;
			$itemObj->TafsiliID = $LoanPersonTafsili;
			$itemObj->SourceType = DOCTYPE_DOCUMENT;
			$itemObj->SourceID = $row["DocumentID"];
			$itemObj->details = $row["DocTypeDesc"];
			$itemObj->Add($pdo);
			
			$SumAmount += $row["ParamValue"]*1;
			$countAmount++;
		}
	}
	if($FirstStep)
	{
		//---------------------------------------------------------		
		$dt = PdoDataAccess::runquery("
			SELECT PayAmount,PayID
				FROM LON_pays
				where PartID=? AND ChequeNo>0",	array($PartObj->PartID), $pdo);

		foreach($dt as $row)
		{
			unset($itemObj->ItemID);
			unset($itemObj->TafsiliType2);
			unset($itemObj->TafsiliID2);
			$itemObj->CostID = $CostCode_guaranteeAmount;
			$itemObj->DebtorAmount = $row["PayAmount"];
			$itemObj->CreditorAmount = 0;
			$itemObj->TafsiliType = TAFTYPE_PERSONS;
			$itemObj->TafsiliID = $LoanPersonTafsili;
			$itemObj->SourceType = DOCTYPE_DOCUMENT;
			$itemObj->SourceID = $row["PayID"];
			$itemObj->Add($pdo);
			
			$SumAmount += $row["ParamValue"]*1;
			$countAmount++;
		}
	}
		//---------------------------------------------------------
	if($SumAmount > 0)
	{
		unset($itemObj->ItemID);
		unset($itemObj->TafsiliType);
		unset($itemObj->TafsiliID);
		unset($itemObj->TafsiliType2);
		unset($itemObj->TafsiliID2);
		unset($itemObj->details);
		$itemObj->CostID = $CostCode_guaranteeAmount2;
		$itemObj->DebtorAmount = 0;
		$itemObj->CreditorAmount = $SumAmount;	
		$itemObj->Add($pdo);

		unset($itemObj->ItemID);
		$itemObj->CostID = $CostCode_guaranteeCount;
		$itemObj->DebtorAmount = $countAmount;
		$itemObj->CreditorAmount = 0;	
		$itemObj->Add($pdo);

		unset($itemObj->ItemID);
		$itemObj->CostID = $CostCode_guaranteeCount2;
		$itemObj->DebtorAmount = 0;
		$itemObj->CreditorAmount = $countAmount;
		$itemObj->Add($pdo);
	}
	
	//------ ایجاد چک ------
	$chequeObj = new ACC_DocChecks();
	$chequeObj->DocID = $obj->DocID;
	$chequeObj->CheckDate = $PartObj->PartDate;
	$chequeObj->amount = $BankItemAmount;
	$chequeObj->TafsiliID = $LoanPersonTafsili;
	$chequeObj->description = " پرداخت " . $PartObj->PartDesc . " وام شماره " . $ReqObj->RequestID;
	$chequeObj->Add($pdo);
	
	//---------------------------------------------------------
	
	if(ExceptionHandler::GetExceptionCount() > 0)
		return false;
	
	return true;
}

function ReturnPayPartDoc($DocID){
	
	return ACC_docs::Remove($DocID);	
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
	$CostCode_commitment = FindCostID("200");
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
	if(!empty($ReqObj->ReqPersonID))
	{
		$PersonObj = new BSC_persons($ReqObj->ReqPersonID);
		if($PersonObj->IsAgent == "YES")
			$LoanMode = "Agent";
	}
	else
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
	/*if($LoanMode == "Supporter")
	{
		$SupporterTafsili = FindTafsiliID($ReqObj->SupportPersonID, TAFTYPE_PERSONS);
		if(!$SupporterTafsili)
		{
			ExceptionHandler::PushException("تفصیلی مربوطه یافت نشد.[" . $ReqObj->SupportPersonID . "]");
			return false;
		}
	}*/
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
		$itemObj->TafsiliType2 = TAFTYPE_PERSONS;
		$itemObj->TafsiliID2 = $ReqPersonTafsili;
	}
	/*if($LoanMode == "Supporter")
	{
		$itemObj->TafsiliType2 = TAFTYPE_PERSONS;
		$itemObj->TafsiliID2 = $SupporterTafsili;
	}*/
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
		unset($itemObj->TafsiliType2);
		unset($itemObj->TafsiliID2);
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
		unset($itemObj->TafsiliType2);
		unset($itemObj->TafsiliID2);
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
			unset($itemObj->TafsiliType2);
			unset($itemObj->TafsiliID2);
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
		unset($itemObj->TafsiliType2);
		unset($itemObj->TafsiliID2);
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
		unset($itemObj->TafsiliType2);
		unset($itemObj->TafsiliID2);
		$itemObj->CostID = $CostCode_commitment;
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

function RegisterCustomerPayDoc($PayObj, $BankTafsili, $pdo){
	
	/*@var $PayObj LON_pays */
	
	$CycleID = substr(DateModules::shNow(), 0 , 4);
	
	$PartObj = new LON_ReqParts($PayObj->PartID);
	$ReqObj = new LON_requests($PartObj->RequestID);
	
	//------------- get CostCodes --------------------
	$LoanObj = new LON_loans($ReqObj->LoanID);
	$CostCode_Loan = FindCostID("110" . "-" . $LoanObj->_BlockCode);
	$CostCode_deposite = FindCostID("210-01");
	$CostCode_bank = FindCostID("101");
	$CostCode_commitment = FindCostID("200");
	
	//---------------- add doc header --------------------
	$obj = new ACC_docs();
	$obj->RegDate = PDONOW;
	$obj->regPersonID = $_SESSION['USER']["PersonID"];
	$obj->DocDate = PDONOW;
	$obj->CycleID = $CycleID;
	$obj->BranchID = $ReqObj->BranchID;
	$obj->DocType = DOCTYPE_INSTALLMENT_PAYMENT;
	$obj->description = "پرداخت قسط " . $PartObj->PartDesc . " وام شماره " . $ReqObj->RequestID . " به نام " .
		$ReqObj->_LoanPersonFullname;
	
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
	if(!empty($ReqObj->ReqPersonID))
	{
		$PersonObj = new BSC_persons($ReqObj->ReqPersonID);
		if($PersonObj->IsAgent == "YES")
			$LoanMode = "Agent";
	}
	else
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
	$itemObj->TafsiliType = TAFTYPE_PERSONS;
	$itemObj->TafsiliID = $LoanPersonTafsili;
	if($LoanMode == "Agent")
	{
		$itemObj->TafsiliType2 = TAFTYPE_PERSONS;
		$itemObj->TafsiliID2 = $ReqPersonTafsili;
	}
	$itemObj->locked = "YES";
	$itemObj->SourceType = DOCTYPE_INSTALLMENT_PAYMENT;
	$itemObj->SourceID = $ReqObj->RequestID;
	$itemObj->SourceID2 = $PayObj->PayID;
	
	//-------- loan ----------
	$itemObj->DocID = $obj->DocID;
	$itemObj->CostID = $CostCode_Loan;
	$itemObj->DebtorAmount = 0;
	$itemObj->CreditorAmount = $PayObj->PayAmount;
	$itemObj->Add($pdo);	
	
	// ---- bank ----
	unset($itemObj->ItemID);
	unset($itemObj->TafsiliType2);
	unset($itemObj->TafsiliID2);
	unset($itemObj->TafsiliID);
	$itemObj->CostID = $CostCode_bank;
	$itemObj->DebtorAmount= $PayObj->PayAmount;
	$itemObj->CreditorAmount = 0;
	$itemObj->TafsiliType = TAFTYPE_BANKS;
	if($BankTafsili != "")
		$itemObj->TafsiliID = $BankTafsili;
	$itemObj->Add($pdo);
	
	if($LoanMode == "Agent")
	{
		//---- اضافه به سپرده -----
		unset($itemObj->ItemID);
		unset($itemObj->TafsiliType2);
		unset($itemObj->TafsiliID2);
		$itemObj->CostID = $CostCode_deposite;
		$itemObj->DebtorAmount = 0;
		$itemObj->CreditorAmount = $PayObj->PayAmount;
		$itemObj->TafsiliType = TAFTYPE_PERSONS;
		$itemObj->TafsiliID = $ReqPersonTafsili;
		if(!$itemObj->Add($pdo))
			return false;

		unset($itemObj->ItemID);
		unset($itemObj->TafsiliType2);
		unset($itemObj->TafsiliID2);
		$itemObj->CostID = $CostCode_commitment;
		$itemObj->DebtorAmount = $PayObj->PayAmount;
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

function ReturnCustomerPayDoc($PayObj, $pdo){
	
	/*@var $PayObj LON_pays */
	
	$dt = PdoDataAccess::runquery("select DocID from ACC_DocItems 
		where SourceType=" . DOCTYPE_INSTALLMENT_PAYMENT . " AND SourceID=? AND SourceID2=?",
		array($PayObj->_RequestID, $PayObj->PayID), $pdo);
	if(count($dt) == 0)
		return true;
	
	return ACC_docs::Remove($dt[0][0], $pdo);
}

function RegisterEndRequestDoc($ReqObj, $pdo){
		
	require_once '../../loan/request/request.data.php';
	
	/*@var $ReqObj LON_requests */
	/*@var $PartObj LON_ReqParts */
	
	//---------- ردیف های تضمین  ----------
	
	$dt = PdoDataAccess::runquery("select * from DMS_documents 
		join BaseInfo b on(InfoID=DocType AND TypeID=8)
		where b.param1=1 AND ObjectType='loan' AND ObjectID=?", array($ReqObj->RequestID));
	
	if(count($dt) > 0)
	{
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
		$obj->DocType = DOCTYPE_END_REQUEST;
		$obj->description = "خاتمه وام شماره " . $ReqObj->RequestID . " به نام " . $ReqObj->_LoanPersonFullname;
		
		if(!$obj->Add($pdo))
		{
			ExceptionHandler::PushException("خطا در ایجاد سند");
			return false;
		}

		//------------------ find tafsilis ---------------
		$LoanPersonTafsili = FindTafsiliID($ReqObj->LoanPersonID, TAFTYPE_PERSONS);
		if(!$LoanPersonTafsili)
		{
			ExceptionHandler::PushException("تفصیلی مربوطه یافت نشد.[" . $ReqObj->LoanPersonID . "]");
			return false;
		}

		//----------------- add Doc items ------------------------
		$itemObj = new ACC_DocItems();
		$itemObj->DocID = $obj->DocID;
		$itemObj->locked = "YES";
		$itemObj->SourceID2 = $ReqObj->RequestID;
		
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
			$itemObj->CostID = $CostCode_guaranteeAmount;
			$itemObj->DebtorAmount = 0;
			$itemObj->CreditorAmount = $row["ParamValue"];
			$itemObj->TafsiliType = TAFTYPE_PERSONS;
			$itemObj->TafsiliID = $LoanPersonTafsili;
			$itemObj->SourceType = DOCTYPE_DOCUMENT;
			$itemObj->SourceID = $row["DocumentID"];
			$itemObj->details = $row["DocTypeDesc"];
			$itemObj->Add($pdo);
			
			$SumAmount += $row["ParamValue"]*1;
		}
		//---------------------------------------------------------		
		$dt2 = PdoDataAccess::runquery("
			SELECT PayAmount,PayID
				FROM LON_pays join LON_ReqParts using(PartID)
				where RequestID=? AND ChequeNo>0",	array($ReqObj->RequestID), $pdo);

		foreach($dt2 as $row)
		{
			unset($itemObj->ItemID);
			unset($itemObj->TafsiliType2);
			unset($itemObj->TafsiliID2);
			$itemObj->CostID = $CostCode_guaranteeAmount;
			$itemObj->DebtorAmount = 0;
			$itemObj->CreditorAmount = $row["PayAmount"];
			$itemObj->TafsiliType = TAFTYPE_PERSONS;
			$itemObj->TafsiliID = $LoanPersonTafsili;
			$itemObj->SourceType = DOCTYPE_DOCUMENT;
			$itemObj->SourceID = $row["PayID"];
			$itemObj->Add($pdo);
			
			$SumAmount += $row["ParamValue"]*1;
		}
		//---------------------------------------------------------
		if($SumAmount > 0)
		{
			unset($itemObj->ItemID);
			unset($itemObj->TafsiliType);
			unset($itemObj->TafsiliID);
			unset($itemObj->TafsiliType2);
			unset($itemObj->TafsiliID2);
			unset($itemObj->details);
			$itemObj->CostID = $CostCode_guaranteeAmount2;
			$itemObj->DebtorAmount = $SumAmount;
			$itemObj->CreditorAmount = 0;	
			$itemObj->Add($pdo);

			unset($itemObj->ItemID);
			$itemObj->CostID = $CostCode_guaranteeCount;
			$itemObj->DebtorAmount = 0;
			$itemObj->CreditorAmount = count($dt) + count($dt2);	
			$itemObj->Add($pdo);

			unset($itemObj->ItemID);
			$itemObj->CostID = $CostCode_guaranteeCount2;
			$itemObj->DebtorAmount = count($dt) + count($dt2);
			$itemObj->CreditorAmount = 0;
			$itemObj->Add($pdo);
		}
	}
	if(ExceptionHandler::GetExceptionCount() > 0)
		return false;
	
	return true;
}

function ReturnEndRequestDoc($ReqObj, $pdo){
		
	$dt = PdoDataAccess::runquery("select d.DocID from ACC_DocItems d join ACC_docs using(DocID)
		where DocType=" . DOCTYPE_END_REQUEST . " AND SourceID2=?",
		array($ReqObj->RequestID), $pdo);
	
	if(count($dt) == 0)
		return true;
	
	return ACC_docs::Remove($dt[0]["DocID"], $pdo);
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
		where DocID>=? AND CostID in(" . COSTID_ShortDeposite . "," . COSTID_LongDeposite . ")
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
		COSTID_ShortDeposite => $dt[0]["ShortDepositPercent"],
		COSTID_LongDeposite  => $dt[0]["LongDepositPercent"]
	);
	
	//------------ get sum of deposites ----------------
	$dt = PdoDataAccess::runquery("select TafsiliID,CostID,sum(CreditorAmount-DebtorAmount) amount
		from ACC_DocItems join ACC_docs using(DocID)
		where DocID<=? 
			AND CostID in(" . COSTID_ShortDeposite . "," . COSTID_LongDeposite . ")
			AND CycleID=" . $_SESSION["accounting"]["CycleID"] . "
			AND BranchID=" . $_SESSION["accounting"]["BranchID"] . "
		group by TafsiliID,CostID", 
		array($LatestComputeDocID));
	$DepositeAmount = array(
		COSTID_ShortDeposite => array(),
		COSTID_LongDeposite => array()
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
		where CostID in(" . COSTID_ShortDeposite . "," . COSTID_LongDeposite . ")
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
	
	foreach($DepositeAmount[ COSTID_ShortDeposite ] as $tafsili => &$row)
	{
		$days = DateModules::GDateMinusGDate(DateModules::Now(), $row["lastDate"]);
		$amount = $row["amount"] * $days * $DepositePercents[ COSTID_ShortDeposite ]/(100*30.5);

		if(!isset($row["profit"]))
			$row["profit"] = 0;
		$row["profit"] += $amount;
		
		//echo $tafsili ."@" . $DepositeAmount[ COSTID_ShortDeposite ][ $tafsili ]["profit"] . "\n";
	}
	foreach($DepositeAmount[ COSTID_LongDeposite ] as $tafsili => &$row)
	{
		$days = DateModules::GDateMinusGDate(DateModules::Now(), $row["lastDate"]);
		$amount = $row["amount"] * $days * $DepositePercents[ COSTID_LongDeposite ]/(100*30.5);

		if(!isset($row["profit"]))
			$row["profit"] = 0;
		$row["profit"] += $amount;
		
		//echo $tafsili ."@" . $DepositeAmount[ COSTID_ShortDeposite ][ $tafsili ]["profit"] . "\n";
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
	$itemObj->CostID = COSTID_Fund;
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

//---------------------------------------------------------------

function ComputeShareProfit(){
	
	//----------- check for all docs confirm --------------
	/*$dt = PdoDataAccess::runquery("select group_concat(distinct LocalNo) from ACC_docs 
		join ACC_DocItems using(DocID)
		where CostID =" . COSTID_share . "
		AND CycleID=" . $_SESSION["accounting"]["CycleID"] . "
		AND DocStatus not in('CONFIRM','ARCHIVE')");
	if(count($dt) > 0 && $dt[0][0] != "")
	{
		echo Response::createObjectiveResponse(false, "اسناد با شماره های [" . $dt[0][0] . "] تایید نشده اند و قادر به صدور سند سود سهام نمی باشید.");
		die();
	}
	*/

	$pdo = PdoDataAccess::getPdoObject();
	$pdo->beginTransaction();

	//--------------- add doc header ------------------
	$obj = new ACC_docs();
	$obj->RegDate = PDONOW;
	$obj->regPersonID = $_SESSION['USER']["PersonID"];
	$obj->DocDate = PDONOW;
	$obj->CycleID = $_SESSION["accounting"]["CycleID"];
	$obj->BranchID = $_SESSION["accounting"]["BranchID"];
	$obj->DocType = DOCTYPE_SHARE_PROFIT;
	$obj->description = "محاسبه سود سهام سهامداران";

	if(!$obj->Add($pdo))
	{
		echo Response::createObjectiveResponse(false, "خطا در ایجاد سند");
		die();
	}
	//------------ compute profits ----------------
	$TotalProfit = $_POST["TotalProfit"];
	
	$dt = PdoDataAccess::runquery("select TafsiliID,sum(CreditorAmount-DebtorAmount) amount
		from ACC_DocItems join ACC_docs using(DocID)
		where CostID=" . COSTID_share . "
			AND CycleID=" . $_SESSION["accounting"]["CycleID"] . "			
		group by TafsiliID
		order by amount");
	
	$TotalShares = 0;
	foreach($dt as $row)
		$TotalShares += $row["amount"];
	
	$sumProfits = 0;
	for($i=0; $i<count($dt);$i++)
	{
		$row = $dt[$i];
		$profit = ceil(($row["amount"]*1/$TotalShares)*$TotalProfit);
		$sumProfits += $profit;
		
		if($i == count($dt)-1)
			$profit += $TotalProfit - $sumProfits;
			
		$itemObj = new ACC_DocItems();
		$itemObj->DocID = $obj->DocID;
		$itemObj->CostID = COSTID_ShareProfit;
		$itemObj->CreditorAmount = $profit;
		$itemObj->DebtorAmount = 0;
		$itemObj->TafsiliType = TAFTYPE_PERSONS;
		$itemObj->TafsiliID = $row["TafsiliID"];
		$itemObj->locked = "YES";
		$itemObj->SourceType = DOCTYPE_SHARE_PROFIT;
		if(!$itemObj->Add($pdo))
		{
			echo Response::createObjectiveResponse(false, "خطا در ایجاد ردیف سند");
			die();
		}
	}
	//---------------------- add fund row ----------------
	/*
	$itemObj = new ACC_DocItems();
	$itemObj->DocID = $obj->DocID;
	$itemObj->CostID = COSTID_Fund;
	$itemObj->DebtorAmount= $sumAmount;
	$itemObj->CreditorAmount = 0;
	$itemObj->locked = "YES";
	$itemObj->SourceType = DOCTYPE_DEPOSIT_PROFIT;
	if(!$itemObj->Add($pdo))
	{
		return false;
	}*/
		
	$pdo->commit();
	echo Response::createObjectiveResponse(true, $obj->DocID);
	die();	
}

?>