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

/**
 *
 * @param jdate $startDate
 * @param jdate $endDate
 * @param type $amount
 * @return type 
 */
function SplitYears($startDate, $endDate, $TotalAmount){
	
	$arr = preg_split('/[\-\/]/',$startDate);
	$StartYear = $arr[0]*1;
	
	$totalDays = 0;
	$yearDays = array();
	$newStartDate = $startDate;
	while(DateModules::CompareDate($newStartDate, $endDate) < 0){
		
		$arr = preg_split('/[\-\/]/',$newStartDate);
		$LastDayOfYear = DateModules::lastJDateOfYear($arr[0]);
		if(DateModules::CompareDate($LastDayOfYear, $endDate) > 0)
			$LastDayOfYear = $endDate;
		
		$yearDays[$StartYear] = DateModules::JDateMinusJDate($LastDayOfYear, $newStartDate)+1;
		$totalDays += $yearDays[$StartYear];
		$StartYear++;
		$newStartDate = DateModules::AddToJDate($LastDayOfYear, 1);
	}
	$TotalDays = DateModules::JDateMinusJDate($endDate, $startDate)+1;
	$sum = 0;
	foreach($yearDays as $year => $days)
	{
		$yearDays[$year] = round(($days/$TotalDays)*$TotalAmount);
		$sum += $yearDays[$year];
	}
	if($sum <> $TotalAmount)
		$yearDays[$year] += $TotalAmount-$sum;
	
	return $yearDays;
}
//---------------------------------------------------------------

function RegisterPayPartDoc($ReqObj, $PartObj, $PayObj, $BankTafsili, $AccountTafsili, $pdo){
		
	require_once '../../loan/request/request.data.php';
	
	/*@var $ReqObj LON_requests */
	/*@var $PartObj LON_ReqParts */
	/*@var $PayObj LON_payments */
	
	$PartObj->MaxFundWage = $PartObj->MaxFundWage*1;
	
	//------------- get CostCodes --------------------
	$LoanObj = new LON_loans($ReqObj->LoanID);
	$CostCode_Loan = FindCostID("110" . "-" . $LoanObj->_BlockCode);
	$CostCode_wage = FindCostID("750" . "-" . $LoanObj->_BlockCode);
	$CostCode_agent_wage = FindCostID("750" . "-52");
	$CostCode_agent_FutureWage = FindCostID("760" . "-52");
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
	
	$CycleID = substr(DateModules::miladi_to_shamsi($PayObj->PayDate), 0 , 4);
	
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
	$PayAmount = $PayObj->PayAmount;
	//--------------------------------------------------------
	$MaxWage = max($PartObj->CustomerWage*1 , $PartObj->FundWage);
	if($PartObj->PayInterval > 0)
		$YearMonths = ($PartObj->IntervalType == "DAY" ) ? floor(365/$PartObj->PayInterval) : 12/$PartObj->PayInterval;
	else
		$YearMonths = 12;
	$TotalWage = round(ComputeWage($PayAmount, $MaxWage/100, 
			$PartObj->InstallmentCount, $YearMonths, $PartObj->PayInterval));	
	
	$CustomerFactor =	$MaxWage == 0 ? 0 : $PartObj->CustomerWage/$MaxWage;
	$FundFactor =		$MaxWage == 0 ? 0 : $PartObj->FundWage/$MaxWage;
	$AgentFactor =		$MaxWage == 0 ? 0 : ($PartObj->CustomerWage-$PartObj->FundWage)/$MaxWage;
	
	///...........................................................
	$years = YearWageCompute($PartObj, $TotalWage*1, $YearMonths);
	if($PartObj->MaxFundWage*1 > 0)
	{
		if($PartObj->WageReturn == "INSTALLMENT")
			$FundYears = YearWageCompute($PartObj, $PartObj->MaxFundWage*1, $YearMonths);
		else 
		{
			$FundYears = array();
			foreach($years as $year => $amount)
				$FundYears[$year] = 0;
		}
	}	
	else
	{
		$FundYears = array();
		foreach($years as $year => $amount)
			$FundYears[$year] = round($FundFactor*$amount);
	}	
	$AgentYears = array();
		foreach($years as $year => $amount)
			$AgentYears[$year] = round($amount - $FundYears[$year]);
	///...........................................................
	
	//$DelayDuration = $PartObj->DelayMonths*1 + $PartObj->DelayDays*1/30;
	$startDate = DateModules::miladi_to_shamsi($PayObj->PayDate);
	$DelayDuration = DateModules::JDateMinusJDate(
		DateModules::AddToJDate($startDate, $PartObj->DelayDays, $PartObj->DelayMonths), $startDate)+1;
		
	$CustomerDelay = round($PayAmount*$PartObj->CustomerWage*$DelayDuration/36500);
	$FundDelay = round($PayAmount*$PartObj->FundWage*$DelayDuration/36500);
	$AgentDelay = round($PayAmount*($PartObj->CustomerWage - $PartObj->FundWage)*$DelayDuration/36500);
	
	$curYear = substr(DateModules::miladi_to_shamsi($PayObj->PayDate), 0, 4)*1;
	$CurYearTafsili = FindTafsiliID($curYear, TAFTYPE_YEARS);
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
		$SubAgentTafsili = "";
		if(!empty($ReqObj->SubAgentID))
		{
			$SubAgentTafsili = FindTafsiliID($ReqObj->SubAgentID, TAFTYPE_SUBAGENT);
			if(!$SubAgentTafsili)
			{
				ExceptionHandler::PushException("تفصیلی زیر واحد سرمایه گذار یافت نشد.[" . $ReqObj->SubAgentID . "]");
				return false;
			}
		}
	}	
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
					. number_format($dt[0][0]) . " ریال می باشد که کمتر از مبلغ این مرحله از پرداخت وام می باشد");
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
	$itemObj->SourceType = DOCTYPE_LOAN_PAYMENT;
	$itemObj->SourceID = $ReqObj->RequestID;
	$itemObj->SourceID2 = $PartObj->PartID;
	$itemObj->SourceID3 = $PayObj->PayID;
	
	$extraAmount = 0;
	if($PartObj->WageReturn == "INSTALLMENT")
	{
		if($PartObj->MaxFundWage*1 > 0)
			$extraAmount += $PartObj->MaxFundWage;
		else if($PartObj->CustomerWage > $PartObj->FundWage)
			$extraAmount += round($TotalWage*$FundFactor);
		else
			$extraAmount += round($TotalWage*$CustomerFactor);
		
	}
		
	if($PartObj->AgentReturn == "INSTALLMENT" && $PartObj->CustomerWage>$PartObj->FundWage)
		$extraAmount += round($TotalWage*$AgentFactor);

	if($PartObj->DelayReturn == "INSTALLMENT")
		$extraAmount += $CustomerDelay*($PartObj->FundWage/$PartObj->CustomerWage);
	if($AgentDelay > 0 && $PartObj->AgentDelayReturn == "INSTALLMENT")
		$extraAmount += $CustomerDelay*(($PartObj->CustomerWage-$PartObj->FundWage)/$PartObj->CustomerWage);
		
	if($FirstStep)
	{
		$amount = $PartObj->PartAmount + $extraAmount;	
		
		unset($itemObj->ItemID);
		$itemObj->DocID = $obj->DocID;
		$itemObj->CostID = $CostCode_Loan;
		$itemObj->DebtorAmount = $amount;
		$itemObj->CreditorAmount = 0;
		$itemObj->Add($pdo);
		$LoanRow = clone $itemObj;
		
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
		if($extraAmount > 0)
		{
			unset($itemObj->ItemID);
			$itemObj->DocID = $obj->DocID;
			$itemObj->CostID = $CostCode_Loan;
			$itemObj->DebtorAmount = $extraAmount;
			$itemObj->CreditorAmount = 0;
			$itemObj->Add($pdo);
			$LoanRow = clone $itemObj;
		}
		
		unset($itemObj->ItemID);
		$itemObj->DocID = $obj->DocID;
		$itemObj->CostID = $CostCode_todiee;
		$itemObj->DebtorAmount = $PayAmount;
		$itemObj->CreditorAmount = 0;
		$itemObj->Add($pdo);
	}
	//------------------------ delay -------------------------------
	if($PartObj->MaxFundWage == 0 && $FundDelay > 0)
	{
		$FundYearDelays = YearDelayCompute($PartObj, $PayObj->PayDate, $PayAmount, $PartObj->FundWage);
		$CustomerYearDelays = YearDelayCompute($PartObj, $PayObj->PayDate, $PayAmount, $PartObj->CustomerWage);
			
		$index = 0;
		while(true)
		{
			$FundYearAmount = isset($FundYearDelays[$curYear+$index]) ? $FundYearDelays[$curYear+$index] : 0;
			$CustomerYearAmount = isset($CustomerYearDelays[$curYear+$index]) ? $CustomerYearDelays[$curYear+$index] : 0;
			$AgentYearAmount = $FundYearAmount > $CustomerYearAmount ? $FundYearAmount - $CustomerYearAmount : 0;
			
			if($FundYearAmount == 0)
				break;

			unset($itemObj->ItemID);
			unset($itemObj->TafsiliType2);
			unset($itemObj->TafsiliID2);
			$itemObj->CostID = $index == 0 ? $CostCode_wage : $CostCode_FutureWage;
			$itemObj->DebtorAmount = 0;
			$itemObj->CreditorAmount = $FundYearAmount;
			$itemObj->TafsiliType = TAFTYPE_YEARS;
			$itemObj->details = "کارمزد دوره تنفس وام شماره " . $ReqObj->RequestID;
			$itemObj->TafsiliID = $index == 0 ? $CurYearTafsili : FindTafsiliID($curYear+$index, TAFTYPE_YEARS);
			if($itemObj->TafsiliID == "")
			{
				ExceptionHandler::PushException("تفصیلی مربوط به سال" . ($curYear+$index) . " یافت نشد");
				return false;
			}		
			if(!$itemObj->Add($pdo))
			{
				//print_r(ExceptionHandler::PopAllExceptions());
				ExceptionHandler::PushException("خطا در ایجاد ردیف کارمزد تنفس");
				return false;
			}

			if($AgentYearAmount > 0)
			{
				unset($itemObj->ItemID);
				$itemObj->CostID = $index == 0 ? $CostCode_agent_wage : $CostCode_agent_FutureWage;
				$itemObj->TafsiliType = TAFTYPE_YEARS;
				$itemObj->TafsiliID = $index == 0 ? $CurYearTafsili : $itemObj->TafsiliID;
				$itemObj->TafsiliType2 = TAFTYPE_PERSONS;
				$itemObj->TafsiliID2 = $ReqPersonTafsili;
				$itemObj->DebtorAmount = $AgentYearAmount;
				$itemObj->CreditorAmount = 0;
				$itemObj->details = "اختلاف کارمزد تنفس وام شماره " . $ReqObj->RequestID;
				if(!$itemObj->Add($pdo))
				{
					ExceptionHandler::PushException("خطا در ایجاد ردیف کارمزد تنفس");
					return false;
				}
			}
			$index++;
		}
	}
	//---------- agent delay ------------	
	if($AgentDelay > 0)
	{
		$AgentYearDelays = YearDelayCompute($PartObj, $PayObj->PayDate, $PayAmount, 
				$PartObj->CustomerWage-$PartObj->FundWage);			
		$index = 0;
		while(true)
		{
			$AgentYearAmount = isset($AgentYearDelays[$curYear+$index]) ? $AgentYearDelays[$curYear+$index] : 0;
			if($AgentYearAmount == 0)
				break;

			unset($itemObj->ItemID);
			$itemObj->CostID = $index == 0 ? $CostCode_agent_wage : $CostCode_agent_FutureWage;
			$itemObj->TafsiliType = TAFTYPE_YEARS;
			$itemObj->TafsiliID = $index == 0 ? $CurYearTafsili : FindTafsiliID($curYear+$index, TAFTYPE_YEARS);
			$itemObj->TafsiliType2 = TAFTYPE_PERSONS;
			$itemObj->TafsiliID2 = $ReqPersonTafsili;
			$itemObj->DebtorAmount = 0;
			$itemObj->CreditorAmount = $AgentYearAmount;
			$itemObj->details = "سهم کارمزد تنفس وام شماره " . $ReqObj->RequestID;
			if(!$itemObj->Add($pdo))
			{
				ExceptionHandler::PushException("خطا در ایجاد ردیف کارمزد تنفس");
				return false;
			}
			$index++;
		}
	}
	//------------------------ کارمزد---------------------	
	if($PartObj->MaxFundWage > 0 && $PartObj->WageReturn == "CUSTOMER")
	{
		unset($itemObj->ItemID);
		$itemObj->details = "کارمزد وام شماره " . $ReqObj->RequestID;
		unset($itemObj->TafsiliType2);
		unset($itemObj->TafsiliID2);
		$itemObj->CostID = $CostCode_wage;
		$itemObj->DebtorAmount = 0;
		$itemObj->CreditorAmount = $PartObj->MaxFundWage;
		$itemObj->TafsiliType = TAFTYPE_YEARS;
		$itemObj->TafsiliID = $YearTafsili;
		$itemObj->Add($pdo);
	}
	else
	{
		foreach($FundYears as $Year => $amount)
		{	
			if($amount == 0)
				continue;
			$YearTafsili = FindTafsiliID($Year, TAFTYPE_YEARS);
			if(!$YearTafsili)
			{
				ExceptionHandler::PushException("تفصیلی مربوطه یافت نشد.[" . $Year . "]");
				return false;
			}
			unset($itemObj->ItemID);
			$itemObj->details = "کارمزد وام شماره " . $ReqObj->RequestID;
			unset($itemObj->TafsiliType2);
			unset($itemObj->TafsiliID2);
			$itemObj->CostID = $Year == $curYear ? $CostCode_wage : $CostCode_FutureWage;
			$itemObj->DebtorAmount = 0;
			$itemObj->CreditorAmount = $amount;
			$itemObj->TafsiliType = TAFTYPE_YEARS;
			$itemObj->TafsiliID = $YearTafsili;
			$itemObj->Add($pdo);
			
			if($PartObj->FundWage*1 > $PartObj->CustomerWage)
			{
				unset($itemObj->ItemID);
				$itemObj->details = "اختلاف کارمزد وام شماره " . $ReqObj->RequestID;
				unset($itemObj->TafsiliType2);
				unset($itemObj->TafsiliID2);
				$itemObj->CostID = $Year == $curYear ? $CostCode_wage : $CostCode_FutureWage;
				$itemObj->DebtorAmount = $amount*$AgentFactor;
				$itemObj->CreditorAmount = 0;
				$itemObj->TafsiliType = TAFTYPE_YEARS;
				$itemObj->TafsiliID = $YearTafsili;
				$itemObj->Add($pdo);
			}
		}
	}
	if($PartObj->AgentReturn == "INSTALLMENT")
	{
		foreach($AgentYears as $Year => $amount)
		{
			if($amount == 0)
				continue;
			$YearTafsili = FindTafsiliID($Year, TAFTYPE_YEARS);
			if(!$YearTafsili)
			{
				ExceptionHandler::PushException("تفصیلی مربوطه یافت نشد.[" . $Year . "]");
				return false;
			}
			unset($itemObj->ItemID);
			$itemObj->details = "سهم کارمزد وام شماره " . $ReqObj->RequestID;
			$itemObj->CostID = $Year == $curYear ? $CostCode_agent_wage : $CostCode_agent_FutureWage;
			$itemObj->DebtorAmount = 0;
			$itemObj->CreditorAmount = $amount;
			$itemObj->TafsiliType = TAFTYPE_YEARS;
			$itemObj->TafsiliID = $YearTafsili;
			$itemObj->TafsiliType2 = TAFTYPE_PERSONS;
			$itemObj->TafsiliID2 = $ReqPersonTafsili;
			$itemObj->Add($pdo);
		}
	}
	if($PartObj->AgentReturn == "CUSTOMER" && $PartObj->CustomerWage > $PartObj->FundWage)
	{
		unset($itemObj->ItemID);
		$itemObj->details = "سهم کارمزد وام شماره " . $ReqObj->RequestID;
		$itemObj->CostID = $CostCode_deposite;
		$itemObj->DebtorAmount = 0;
		$itemObj->CreditorAmount = $TotalWage*$AgentFactor;
		$itemObj->TafsiliType = TAFTYPE_PERSONS;
		$itemObj->TafsiliID = $ReqPersonTafsili;
		$itemObj->Add($pdo);
	}
	// ----------------------------- bank --------------------------------
	$itemObj = new ACC_DocItems();
	$itemObj->DocID = $obj->DocID;
	$itemObj->CostID = $CostCode_bank;
	$itemObj->DebtorAmount = 0;
	
	$BankItemAmount = $PayAmount;
	if($PartObj->WageReturn == "CUSTOMER")
	{
		if($PartObj->MaxFundWage > 0)
			$BankItemAmount -= $PartObj->MaxFundWage;
		else if($PartObj->CustomerWage > $PartObj->FundWage)
			$BankItemAmount -= $TotalWage*$FundFactor;
		else
			$BankItemAmount -= $TotalWage*$CustomerFactor;
	}
	if($PartObj->AgentReturn == "CUSTOMER" && $PartObj->CustomerWage > $PartObj->FundWage)
		$BankItemAmount -= $TotalWage*$AgentFactor;
	
	if($PartObj->DelayReturn == "CUSTOMER")
		$BankItemAmount -= $FundDelay;
	if($PartObj->AgentDelayReturn == "CUSTOMER")
		$BankItemAmount -= $AgentDelay;

	$itemObj->CreditorAmount = $BankItemAmount;
	$itemObj->TafsiliType = TAFTYPE_BANKS;
	$itemObj->TafsiliID = $BankTafsili;
	$itemObj->TafsiliType2 = TAFTYPE_ACCOUNTS;
	$itemObj->TafsiliID = $AccountTafsili;
	$itemObj->locked = "YES";
	$itemObj->SourceType = DOCTYPE_LOAN_PAYMENT;
	$itemObj->SourceID = $ReqObj->RequestID;
	$itemObj->SourceID2 = $PartObj->PartID;
	$itemObj->SourceID3 = $PayObj->PayID;
	$itemObj->Add($pdo);
	$BankRow = clone $itemObj;
	
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
		if($SubAgentTafsili != "")
		{
			$itemObj->TafsiliType2 = TAFTYPE_SUBAGENT;
			$itemObj->TafsiliID2 = $SubAgentTafsili;
		}
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
	$SumAmount = 0;
	$countAmount = 0;
	
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
			SELECT PayAmount,BackPayID
				FROM LON_BackPays
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
			$itemObj->SourceID = $row["BackPayID"];
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
	$chequeObj = new ACC_DocCheques();
	$chequeObj->DocID = $obj->DocID;
	$chequeObj->CheckDate = $PayObj->PayDate;
	$chequeObj->amount = $BankItemAmount;
	$chequeObj->TafsiliID = $LoanPersonTafsili;
	$chequeObj->description = " پرداخت " . $PartObj->PartDesc . " وام شماره " . $ReqObj->RequestID;
	$chequeObj->Add($pdo);
	
	//---------------------------------------------------------
	$dt = PdoDataAccess::runquery("select sum(DebtorAmount) dsum, sum(CreditorAmount) csum
		from ACC_DocItems where DocID=?", array($obj->DocID), $pdo);
	if($dt[0]["dsum"] > $dt[0]["csum"])
	{
		$BankRow->CreditorAmount += $dt[0]["dsum"] - $dt[0]["csum"];
		$BankRow->Edit($pdo);
	}
	else if($dt[0]["csum"] > $dt[0]["dsum"])
	{
		$LoanRow->DebtorAmount += $dt[0]["csum"] - $dt[0]["dsum"];
		$LoanRow->Edit($pdo);
	}
	//---------------------------------------------------------
	
	if(ExceptionHandler::GetExceptionCount() > 0)
		return false;
	
	return $obj->DocID;
}

function ReturnPayPartDoc($DocID, $pdo){
	
	return ACC_docs::Remove($DocID, $pdo);	
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
	//$DelayDuration = $PartObj->DelayMonths*1 + $PartObj->DelayDays*1/30;
	$startDate = DateModules::miladi_to_shamsi($PartObj->PartDate);
	$DelayDuration = DateModules::JDateMinusJDate(
		DateModules::AddToJDate($startDate, $PartObj->DelayDays, $PartObj->DelayMonths), $startDate)+1;
	
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
	
	$TotalDelay = round($PartObj->PartAmount*$PartObj->CustomerWage*$DelayDuration/36500);
	$curYear = substr(DateModules::miladi_to_shamsi($PartObj->PartDate), 0, 4)*1;
	
	//--------------------- compute for new Amount ---------------------

	$new_TotalWage = round(ComputeWage($PaidAmount, $PartObj->CustomerWage/100, 
			$installmentCount, $YearMonths));	
	$PartObj->InstallmentCount = $installmentCount;
	$new_year1 = $FundFactor*YearWageCompute($PartObj, $new_TotalWage, 1, $YearMonths);
	$new_year2 = $FundFactor*YearWageCompute($PartObj, $new_TotalWage, 2, $YearMonths);
	$new_year3 = $FundFactor*YearWageCompute($PartObj, $new_TotalWage, 3, $YearMonths);
	$new_year4 = $FundFactor*YearWageCompute($PartObj, $new_TotalWage, 4, $YearMonths);
	
	$new_TotalDelay = round($PaidAmount*$PartObj->CustomerWage*$DelayDuration/36500);
	
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
		$SubAgentTafsili = "";
		if(!empty($ReqObj->SubAgentID))
		{
			$SubAgentTafsili = FindTafsiliID($ReqObj->SubAgentID, TAFTYPE_SUBAGENT);
			if(!$SubAgentTafsili)
			{
				ExceptionHandler::PushException("تفصیلی زیر واحد سرمایه گذار یافت نشد.[" . $ReqObj->SubAgentID . "]");
				return false;
			}
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
			if($SubAgentTafsili != "")
			{
				$itemObj->TafsiliType2 = TAFTYPE_SUBAGENT;
				$itemObj->TafsiliID2 = $SubAgentTafsili;
			}
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
		if($SubAgentTafsili != "")
		{
			$itemObj->TafsiliType2 = TAFTYPE_SUBAGENT;
			$itemObj->TafsiliID2 = $SubAgentTafsili;
		}
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
		if($SubAgentTafsili != "")
		{
			$itemObj->TafsiliType2 = TAFTYPE_SUBAGENT;
			$itemObj->TafsiliID2 = $SubAgentTafsili;
		}
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

function RegisterCustomerPayDoc($DocObj, $PayObj, $BankTafsili, $AccountTafsili,  $pdo){
	
	/*@var $PayObj LON_BackPays */
	$PartObj = new LON_ReqParts($PayObj->PartID);
	$ReqObj = new LON_requests($PartObj->RequestID);
	
	if($DocObj == null)
	{
		$dt = PdoDataAccess::runquery("select * from ACC_DocItems where SourceType=" . DOCTYPE_INSTALLMENT_PAYMENT . " 
			AND SourceID=? AND SourceID2=?" , array($ReqObj->RequestID, $PayObj->BackPayID));
		if(count($dt) > 0)
		{
			ExceptionHandler::PushException("سند این ردیف پرداخت قبلا صادر شده است");
			return false;
		}
	}
	
	$CycleID = substr(DateModules::shNow(), 0 , 4);
	
	//------------- get CostCodes --------------------
	$LoanObj = new LON_loans($ReqObj->LoanID);
	$CostCode_Loan = FindCostID("110" . "-" . $LoanObj->_BlockCode);
	$CostCode_deposite = FindCostID("210-01");
	$CostCode_bank = FindCostID("101");
	$CostCode_commitment = FindCostID("200-01");
	
	//---------------- add doc header --------------------
	if($DocObj == null)
	{
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
		{
			ExceptionHandler::PushException("خطا در ایجاد سند");
			return false;
		}
	}
	else
		$obj = $DocObj;
	
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
		$SubAgentTafsili = "";
		if(!empty($ReqObj->SubAgentID))
		{
			$SubAgentTafsili = FindTafsiliID($ReqObj->SubAgentID, TAFTYPE_SUBAGENT);
			if(!$SubAgentTafsili)
			{
				ExceptionHandler::PushException("تفصیلی زیر واحد سرمایه گذار یافت نشد.[" . $ReqObj->SubAgentID . "]");
				return false;
			}
		}
	}
	//----------------- add Doc items ------------------------
		
	$itemObj = new ACC_DocItems();
	$itemObj->DocID = $obj->DocID;
	$itemObj->details = "پرداخت قسط وام شماره " . $ReqObj->RequestID ;
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
	$itemObj->SourceID2 = $PayObj->BackPayID;
	
	//-------- loan ----------
	$itemObj->DocID = $obj->DocID;
	$itemObj->CostID = $CostCode_Loan;
	$itemObj->DebtorAmount = 0;
	$itemObj->CreditorAmount = $PayObj->PayAmount;
	if(!$itemObj->Add($pdo))
	{
		ExceptionHandler::PushException("خطا در ایجاد سند");
		return false;
	}
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
	$itemObj->TafsiliType2 = TAFTYPE_ACCOUNTS;
	if($AccountTafsili != "")
		$itemObj->TafsiliID2 = $AccountTafsili;
	if(!$itemObj->Add($pdo))
	{
		ExceptionHandler::PushException("خطا در ایجاد سند");
		return false;
	}
	
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
		if($SubAgentTafsili != "")
		{
			$itemObj->TafsiliType2 = TAFTYPE_SUBAGENT;
			$itemObj->TafsiliID2 = $SubAgentTafsili;
		}
		if(!$itemObj->Add($pdo))
		{
			ExceptionHandler::PushException("خطا در ایجاد سند");
			return false;
		}

		unset($itemObj->ItemID);
		unset($itemObj->TafsiliType2);
		unset($itemObj->TafsiliID2);
		$itemObj->CostID = $CostCode_commitment;
		$itemObj->DebtorAmount = $PayObj->PayAmount;
		$itemObj->CreditorAmount = 0;
		$itemObj->TafsiliType = TAFTYPE_PERSONS;
		$itemObj->TafsiliID = $ReqPersonTafsili;
		if($SubAgentTafsili != "")
		{
			$itemObj->TafsiliType2 = TAFTYPE_SUBAGENT;
			$itemObj->TafsiliID2 = $SubAgentTafsili;
		}
		if(!$itemObj->Add($pdo))
		{
			ExceptionHandler::PushException("خطا در ایجاد سند");
			return false;
		}
	}
	
	//---------------------------------------------------------
	if(ExceptionHandler::GetExceptionCount() > 0)
		return false;
		
	return true;
}

function ReturnCustomerPayDoc($PayObj, $pdo, $EditMode = false){
	
	/*@var $PayObj LON_BackPays */
	
	$dt = PdoDataAccess::runquery("select DocID from ACC_DocItems 
		where SourceType=" . DOCTYPE_INSTALLMENT_PAYMENT . " AND SourceID=? AND SourceID2=?",
		array($PayObj->_RequestID, $PayObj->BackPayID), $pdo);
	if(count($dt) == 0)
		return true;
	
	if($EditMode)
	{
		PdoDataAccess::runquery("delete from ACC_DocItems 
			where SourceType=" . DOCTYPE_INSTALLMENT_PAYMENT . " AND SourceID=? AND SourceID2=?",
			array($PayObj->_RequestID, $PayObj->BackPayID), $pdo);
		return true;
	}
	
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
			SELECT PayAmount,BackPayID
				FROM LON_BackPays join LON_ReqParts using(PartID)
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
			$itemObj->SourceID = $row["BackPayID"];
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
	echo Response::createObjectiveResponse(true, $obj->LocalNo);
	die();	
}

//---------------------------------------------------------------

function ComputeShareProfit(){
	
	//----------- check for all docs confirm --------------
	$dt = PdoDataAccess::runquery("select group_concat(distinct LocalNo) from ACC_docs 
		join ACC_DocItems using(DocID)
		where CostID =" . COSTID_share . "
		AND CycleID=" . $_SESSION["accounting"]["CycleID"] . "
		AND DocStatus not in('CONFIRM','ARCHIVE')");
	if(count($dt) > 0 && $dt[0][0] != "")
	{
		echo Response::createObjectiveResponse(false, "اسناد با شماره های [" . $dt[0][0] . "] تایید نشده اند و قادر به صدور سند سود سهام نمی باشید.");
		die();
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

//---------------------------------------------------------------

function RegisterWarrantyDoc($ReqObj, $WageCost,$BankTafsili, $AccountTafsili, $DocID, $pdo){
	
	/*@var $ReqObj WAR_requests */
	
	//------------- get CostCodes --------------------
	$CostCode_warrenty = FindCostID("300");
	$CostCode_warrenty_commitment = FindCostID("700");
	$CostCode_wage = FindCostID("750-07");
	$CostCode_FutureWage = FindCostID("760-07");
	$CostCode_fund = FindCostID("100");
	$CostCode_pasandaz = FindCostID("209-10");
	$CostCode_guaranteeAmount = FindCostID("904-02");
	$CostCode_guaranteeCount = FindCostID("904-01");
	$CostCode_guaranteeAmount2 = FindCostID("905-02");
	$CostCode_guaranteeCount2 = FindCostID("905-01");
	//------------------------------------------------
	$CycleID = substr(DateModules::miladi_to_shamsi($ReqObj->StartDate), 0 , 4);
	$BranchID = "1";
	//------------------ find tafsilis ---------------
	$PersonTafsili = FindTafsiliID($ReqObj->PersonID, TAFTYPE_PERSONS);
	if(!$PersonTafsili)
	{
		ExceptionHandler::PushException("تفصیلی مربوطه یافت نشد.[" . $ReqObj->PersonID . "]");
		return false;
	}
	
	//------------------- compute wage ------------------
	$days = DateModules::GDateMinusGDate($ReqObj->EndDate,$ReqObj->StartDate);
	$TotalWage = round($days*$ReqObj->amount*$ReqObj->wage/36500);	
	
	$years = SplitYears(DateModules::miladi_to_shamsi($ReqObj->StartDate), 
		DateModules::miladi_to_shamsi($ReqObj->EndDate), $TotalWage);
	
	//--------------- check pasandaz remaindar -----------------
	
	$dt = PdoDataAccess::runquery("select sum(CreditorAmount-DebtorAmount) remain
		from ACC_DocItems join ACC_docs using(DocID) where CycleID=? AND CostID=?
			AND TafsiliType=? AND TafsiliID=?", array(
				$CycleID,
				$CostCode_pasandaz,
				TAFTYPE_PERSONS,
				$PersonTafsili
			));
	if($dt[0][0]*1 < $ReqObj->amount*0.1)
	{
		ExceptionHandler::PushException("مانده حساب پس انداز مشتری کمتر از 10% مبلغ ضمانت نامه می باشد");
		return false;
	}
	if($WageCost == "209-10" && $dt[0][0]*1 < ($ReqObj->amount*0.1 + $TotalWage))
	{
		ExceptionHandler::PushException("مانده حساب پس انداز مشتری کمتر از مبلغ کارمزد می باشد");
		return false;
	}
	//---------------- add doc header --------------------
	if($DocID == null)
	{
		$DocObj = new ACC_docs();
		$DocObj->RegDate = PDONOW;
		$DocObj->regPersonID = $_SESSION['USER']["PersonID"];
		$DocObj->DocDate = PDONOW;
		$DocObj->CycleID = $CycleID;
		$DocObj->BranchID = $BranchID;
		$DocObj->DocType = DOCTYPE_WARRENTY;
		$DocObj->description = "ضمانت نامه شماره " . $ReqObj->RequestID . " به نام " . $ReqObj->_fullname;

		if(!$DocObj->Add($pdo))
		{
			ExceptionHandler::PushException("خطا در ایجاد سند");
			return false;
		}
	}
	else
		$DocObj = new ACC_docs($DocID);
	//----------------- add Doc items ------------------------
	$itemObj = new ACC_DocItems();
	$itemObj->DocID = $DocObj->DocID;
	$itemObj->TafsiliType = TAFTYPE_PERSONS;
	$itemObj->TafsiliID = $PersonTafsili;
	$itemObj->SourceType = DOCTYPE_WARRENTY;
	$itemObj->SourceID = $ReqObj->RequestID;
	$itemObj->SourceID2 = $ReqObj->ReqVersion;
	$itemObj->locked = "YES";
	
	$itemObj->CostID = $CostCode_warrenty;
	$itemObj->DebtorAmount = $ReqObj->amount;
	$itemObj->CreditorAmount = 0;
	if(!$itemObj->Add($pdo))
	{
		ExceptionHandler::PushException("خطا در ثبت ردیف ضمانت نامه");
		return false;
	}
	
	unset($itemObj->ItemID);
	$itemObj->CostID = $CostCode_warrenty_commitment;
	$itemObj->DebtorAmount = 0;
	$itemObj->CreditorAmount = $ReqObj->amount;
	if(!$itemObj->Add($pdo))
	{
		ExceptionHandler::PushException("خطا در ثبت ردیف تعهد ضمانت نامه");
		return false;
	}
	
	unset($itemObj->ItemID);
	$itemObj->CostID = $CostCode_pasandaz;
	$itemObj->DebtorAmount = $ReqObj->amount*0.1;
	$itemObj->CreditorAmount = 0;
	if(!$itemObj->Add($pdo))
	{
		ExceptionHandler::PushException("خطا در ثبت ردیف تعهد ضمانت نامه");
		return false;
	}
	
	unset($itemObj->ItemID);
	$itemObj->CostID = $CostCode_fund;
	$itemObj->DebtorAmount = 0;
	$itemObj->CreditorAmount = $ReqObj->amount*0.1;
	if(!$itemObj->Add($pdo))
	{
		ExceptionHandler::PushException("خطا در ثبت ردیف تعهد ضمانت نامه");
		return false;
	}
	//------------------- compute wage -----------------------
	$curYear = substr(DateModules::miladi_to_shamsi($ReqObj->StartDate), 0, 4)*1;
	foreach($years as $Year => $amount)
	{	
		if($amount == 0)
			continue;
		$YearTafsili = FindTafsiliID($Year, TAFTYPE_YEARS);
		if(!$YearTafsili)
		{
			ExceptionHandler::PushException("تفصیلی مربوطه یافت نشد.[" . $Year . "]");
			return false;
		}
		unset($itemObj->ItemID);
		$itemObj->details = "کارمزد ضمانت نامه شماره " . $ReqObj->RequestID;
		$itemObj->CostID = $Year == $curYear ? $CostCode_wage : $CostCode_FutureWage;
		$itemObj->DebtorAmount = 0;
		$itemObj->CreditorAmount = $amount;
		$itemObj->TafsiliType = TAFTYPE_YEARS;
		$itemObj->TafsiliID = $YearTafsili;
		$itemObj->Add($pdo);
	}
	
	// ----------------------------- bank --------------------------------

	unset($itemObj->ItemID);
	unset($itemObj->TafsiliType);
	unset($itemObj->TafsiliID);
	$itemObj->details = "بابت کارمزد ضمانت نامه شماره " . $ReqObj->RequestID;
	$itemObj->CostID = FindCostID($WageCost);
	$itemObj->DebtorAmount = $TotalWage;
	$itemObj->CreditorAmount = 0;
	
	if($WageCost == "101")
	{
		$itemObj->TafsiliType = TAFTYPE_BANKS;
		if($BankTafsili != "")
			$itemObj->TafsiliID = $BankTafsili;
		$itemObj->TafsiliType2 = TAFTYPE_ACCOUNTS;
		if($AccountTafsili != "")
			$itemObj->TafsiliID2 = $AccountTafsili;
	}
	$itemObj->Add($pdo);
	
	//---------- ردیف های تضمین  ----------

	$SumAmount = 0;
	$countAmount = 0;	
	$dt = PdoDataAccess::runquery("
		SELECT DocumentID, ParamValue, InfoDesc as DocTypeDesc
			FROM DMS_DocParamValues
			join DMS_DocParams using(ParamID)
			join DMS_documents d using(DocumentID)
			join BaseInfo b on(InfoID=d.DocType AND TypeID=8)
			left join ACC_DocItems on(SourceType=" . DOCTYPE_DOCUMENT . " AND SourceID=DocumentID)
		where ItemID is null AND b.param1=1 AND 
			paramType='currencyfield' AND ObjectType='warrenty' AND ObjectID=?",array($ReqObj->RequestID), $pdo);

	foreach($dt as $row)
	{
		unset($itemObj->ItemID);
		$itemObj->CostID = $CostCode_guaranteeAmount;
		$itemObj->DebtorAmount = $row["ParamValue"];
		$itemObj->CreditorAmount = 0;
		$itemObj->TafsiliType = TAFTYPE_PERSONS;
		$itemObj->TafsiliID = $PersonTafsili;
		$itemObj->SourceType = DOCTYPE_DOCUMENT;
		$itemObj->SourceID = $row["DocumentID"];
		$itemObj->details = $row["DocTypeDesc"];
		$itemObj->Add($pdo);

		$SumAmount += $row["ParamValue"]*1;
		$countAmount++;
	}
	if($SumAmount > 0)
	{
		unset($itemObj->ItemID);
		unset($itemObj->TafsiliType);
		unset($itemObj->TafsiliID);
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
	
	if(ExceptionHandler::GetExceptionCount() > 0)
		return false;
	
	return $DocObj->DocID;
}

function ReturnWarrantyDoc($ReqObj, $pdo, $EditMode = false){
	
	/*@var $PayObj WAR_requests */
	
	$dt = PdoDataAccess::runquery("select DocID from ACC_DocItems 
		where SourceType=" . DOCTYPE_WARRENTY . " AND SourceID=? AND SourceID2=?",
		array($ReqObj->RequestID, $ReqObj->ReqVersion), $pdo);
	if(count($dt) == 0)
		return true;
	
	if($EditMode)
	{
		PdoDataAccess::runquery("delete from ACC_DocItems 
			where SourceType=" . DOCTYPE_WARRENTY . " AND SourceID=? AND SourceID2=?",
			array($ReqObj->RequestID, $ReqObj->ReqVersion), $pdo);
		return true;
	}
	
	return ACC_docs::Remove($dt[0][0], $pdo);
}

?>
