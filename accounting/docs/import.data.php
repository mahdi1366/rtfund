<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 94.06
//-----------------------------
 
require_once '../header.inc.php';
require_once getenv("DOCUMENT_ROOT") . '/framework/person/persons.class.php';
require_once getenv("DOCUMENT_ROOT") . '/loan/loan/loan.class.php';
require_once getenv("DOCUMENT_ROOT") . '/loan/request/compute.inc.php';
require_once getenv("DOCUMENT_ROOT") . '/accounting/docs/doc.class.php';
require_once getenv("DOCUMENT_ROOT") . '/accounting/baseinfo/baseinfo.class.php';

require_once inc_dataReader;
require_once inc_response;

function FindCostID($costCode){
	
	$dt = PdoDataAccess::runquery("select * from ACC_CostCodes where IsActive='YES' AND CostCode=?",
		array($costCode));
	
	return count($dt) == 0 ? false : $dt[0]["CostID"];
}

function FindTafsiliID($TafsiliCode, $TafsiliType){
	
	$dt = PdoDataAccess::runquery("select * from ACC_tafsilis "
			. "where IsActive='YES' AND TafsiliCode=? AND TafsiliType=?",
		array($TafsiliCode, $TafsiliType));
	
	if(count($dt) == 0)
	{
		ExceptionHandler::PushException("تفصیلی مربوطه یافت نشد.[" . $TafsiliCode . "]");
		return false;
	}
	
	return $dt[0]["TafsiliID"];
}

//---------------------------------------------------------------

function RegisterPayPartDoc($ReqObj, $PartObj, $PayObj, $BankTafsili, $AccountTafsili, $pdo, $DocID=""){
		
	
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
	$CostCode_commitment = FindCostID("200-" . $LoanObj->_BlockCode . "-51");
	$CostCode_todiee = FindCostID("200-". $LoanObj->_BlockCode."-01");
	
	$CostCode_guaranteeAmount_zemanati = FindCostID("904-02");
	$CostCode_guaranteeAmount2_zemanati = FindCostID("905-02");
	
	//------------------------------------------------
	$CycleID = substr(DateModules::miladi_to_shamsi($PayObj->PayDate), 0 , 4);
	$PayAmount = $PayObj->PayAmount;
	//------------------- find load mode ---------------------
	$LoanMode = "";
	if(!empty($ReqObj->ReqPersonID))
	{
		$PersonObj = new BSC_persons($ReqObj->ReqPersonID);
		if($PersonObj->IsAgent == "YES")
			$LoanMode = "Agent";
	}
	else
		$LoanMode = "Customer";
	//------------------ find tafsilis ---------------
	$LoanPersonTafsili = FindTafsiliID($ReqObj->LoanPersonID, TAFTYPE_PERSONS);
	if(!$LoanPersonTafsili)
		return false;
	
	if($LoanMode == "Agent")
	{
		$ReqPersonTafsili = FindTafsiliID($ReqObj->ReqPersonID, TAFTYPE_PERSONS);
		if(!$ReqPersonTafsili)
			return false;
		$SubAgentTafsili = "";
		if(!empty($ReqObj->SubAgentID))
		{
			$SubAgentTafsili = FindTafsiliID($ReqObj->SubAgentID, TAFTYPE_SUBAGENT);
			if(!$SubAgentTafsili)
				return false;
		}
	}	
	//------------ find the number step to pay ---------------
	$FirstStep = true;
	$dt = PdoDataAccess::runquery("select * from ACC_DocItems where SourceType=" .
			DOCTYPE_LOAN_PAYMENT . " AND SourceID=? order by SourceID3", 
			array($ReqObj->RequestID));
	if(count($dt) > 0)
	{
		$FirstStep = false;
		$firstPayObj = new LON_payments($dt[0]["SourceID3"]);
		
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
	//---------------- add doc header --------------------
	if($DocID == "")
	{
		$obj = new ACC_docs();
		$obj->RegDate = PDONOW;
		$obj->regPersonID = $_SESSION['USER']["PersonID"];
		$obj->DocDate = PDONOW;
		$obj->CycleID = $CycleID;
		$obj->BranchID = $ReqObj->BranchID;
		$obj->DocType = DOCTYPE_LOAN_PAYMENT;
		$obj->description = "پرداخت وام شماره " . $ReqObj->RequestID . " به نام " . 
			$ReqObj->_LoanPersonFullname;

		if(!$obj->Add($pdo))
		{
			ExceptionHandler::PushException("خطا در ایجاد سند");
			return false;
		}
	}
	else
		$obj = new ACC_docs($DocID);
	//--------------------------------------------------------
	$MaxWage = max($PartObj->CustomerWage*1 , $PartObj->FundWage);
	$AgentFactor = $MaxWage == 0 ? 0 : ($PartObj->CustomerWage-$PartObj->FundWage)/$MaxWage;
	
	$firstPayDate = $FirstStep ? $PayObj->PayDate : $firstPayObj->PayDate;
	$result = ComputeWagesAndDelays($PartObj, $PayAmount, $firstPayDate, $PayObj->PayDate);
	$TotalFundWage = $result["TotalFundWage"];
	$TotalAgentWage = $result["TotalAgentWage"];
	$TotalCustomerWage = $result["TotalCustomerWage"];
	$FundYears = $result["FundWageYears"];
	$AgentYears = $result["AgentWageYears"];
	
	$TotalFundDelay = $result["TotalFundDelay"];
	$TotalAgentDelay = $result["TotalAgentDelay"];
	$CustomerYearDelays = $result["CustomerYearDelays"];
	///...........................................................
	$curYear = substr(DateModules::miladi_to_shamsi($PayObj->PayDate), 0, 4)*1;
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
			$extraAmount += $TotalFundWage;
		else
			$extraAmount += $TotalCustomerWage;
	}
		
	if($PartObj->AgentReturn == "INSTALLMENT" && $PartObj->CustomerWage>$PartObj->FundWage)
		$extraAmount += $TotalAgentWage;

	if($PartObj->DelayReturn == "INSTALLMENT")
		$extraAmount += $TotalFundDelay;
	if($TotalAgentDelay > 0 && $PartObj->AgentDelayReturn == "INSTALLMENT")
		$extraAmount += $TotalAgentDelay;
		
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
	$totalAgentYearAmount = 0;
	if($PartObj->MaxFundWage == 0 && $TotalFundDelay > 0)
	{
		$index = 0;
		foreach($CustomerYearDelays as $year => $value)
		{
			$FundYearAmount = ($PartObj->FundWage/$PartObj->DelayPercent)*$value;
			$AgentYearAmount = (($PartObj->CustomerWage*1-$PartObj->FundWage*1)/$PartObj->DelayPercent*1)*$value;
			
			if($FundYearAmount == 0)
				break;

			unset($itemObj->ItemID);
			unset($itemObj->TafsiliType2);
			unset($itemObj->TafsiliID2);
			$itemObj->CostID = $year == $curYear ? $CostCode_wage : $CostCode_FutureWage;
			$itemObj->DebtorAmount = 0;
			$itemObj->CreditorAmount = $FundYearAmount;
			$itemObj->TafsiliType = TAFTYPE_YEARS;
			$itemObj->details = "کارمزد دوره تنفس وام شماره " . $ReqObj->RequestID;
			$itemObj->TafsiliID = FindTafsiliID($year, TAFTYPE_YEARS);
			if($itemObj->TafsiliID == "")
			{
				ExceptionHandler::PushException("تفصیلی مربوط به سال" . ($curYear+$index) . " یافت نشد");
				return false;
			}		
			if(!$itemObj->Add($pdo))
			{
				ExceptionHandler::PushException("خطا در ایجاد ردیف کارمزد تنفس");
				return false;
			}
			$totalAgentYearAmount += $AgentYearAmount;
			$index++;
		}
		if($totalAgentYearAmount < 0)
		{
			unset($itemObj->ItemID);
			$itemObj->CostID = $CostCode_agent_wage;
			$itemObj->TafsiliType2 = TAFTYPE_PERSONS;
			$itemObj->TafsiliID2 = $ReqPersonTafsili;
			$itemObj->DebtorAmount = abs($AgentYearAmount);
			$itemObj->CreditorAmount = 0;
			$itemObj->TafsiliID = FindTafsiliID($curYear, TAFTYPE_YEARS);
			$itemObj->details = "اختلاف کارمزد تنفس وام شماره " . $ReqObj->RequestID;
			if(!$itemObj->Add($pdo))
			{
				ExceptionHandler::PushException("خطا در ایجاد ردیف کارمزد تنفس");
				return false;
			}
		}
		if($totalAgentYearAmount > 0)
		{
			unset($itemObj->ItemID);
			$itemObj->TafsiliType2 = TAFTYPE_PERSONS;
			$itemObj->TafsiliID2 = $ReqPersonTafsili;
			$itemObj->CostID = $CostCode_agent_wage;
			$itemObj->DebtorAmount = 0;
			$itemObj->CreditorAmount = $totalAgentYearAmount;
			$itemObj->TafsiliID = FindTafsiliID($curYear, TAFTYPE_YEARS);
			$itemObj->details = "سهم کارمزد تنفس وام شماره " . $ReqObj->RequestID;
			if(!$itemObj->Add($pdo))
			{
				ExceptionHandler::PushException("خطا در ایجاد ردیف کارمزد تنفس");
				return false;
			}
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
			if($amount <= 0)
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
				if($LoanMode == "Agent")
				{
					$itemObj->TafsiliType2 = TAFTYPE_PERSONS;
					$itemObj->TafsiliID2 = $ReqPersonTafsili;
				}
				$itemObj->Add($pdo);
			}
		}
		
		if($PartObj->WageReturn == "AGENT")
		{
			unset($itemObj->ItemID);
			$itemObj->details = " کارمزد وام شماره " . $ReqObj->RequestID;
			$itemObj->CostID = $CostCode_deposite;
			$itemObj->DebtorAmount = $TotalFundWage;
			$itemObj->CreditorAmount = 0;
			$itemObj->TafsiliType = TAFTYPE_PERSONS;
			$itemObj->TafsiliID = $ReqPersonTafsili;
			unset($itemObj->TafsiliType2);
			unset($itemObj->TafsiliID2);
			$itemObj->Add($pdo);
		}
	}
	if($LoanMode == "Agent" && $PartObj->AgentReturn == "INSTALLMENT")
	{
		foreach($AgentYears as $Year => $amount)
		{
			if($amount <= 0)
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
	if($LoanMode == "Agent" && $PartObj->AgentReturn == "CUSTOMER" && $PartObj->CustomerWage > $PartObj->FundWage)
	{
		unset($itemObj->ItemID);
		$itemObj->details = "سهم کارمزد وام شماره " . $ReqObj->RequestID;
		$itemObj->CostID = $CostCode_deposite;
		$itemObj->DebtorAmount = 0;
		$itemObj->CreditorAmount = $TotalAgentWage;
		$itemObj->TafsiliType = TAFTYPE_PERSONS;
		$itemObj->TafsiliID = $ReqPersonTafsili;
		if($LoanMode == "Agent")
		{
			$itemObj->TafsiliType2 = TAFTYPE_PERSONS;
			$itemObj->TafsiliID2 = $ReqPersonTafsili;
		}
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
			$BankItemAmount -= $TotalFundWage;
		else
			$BankItemAmount -= $TotalCustomerWage;
	}
	if($PartObj->AgentReturn == "CUSTOMER" && $PartObj->CustomerWage > $PartObj->FundWage)
		$BankItemAmount -= $TotalAgentWage;
	
	if($PartObj->DelayReturn == "CUSTOMER")
		$BankItemAmount -= $TotalFundDelay;
	if($PartObj->AgentDelayReturn == "CUSTOMER")
		$BankItemAmount -= $TotalAgentDelay;

	$itemObj->CreditorAmount = $BankItemAmount;
	$itemObj->TafsiliType = TAFTYPE_BANKS;
	$itemObj->TafsiliID = $BankTafsili;
	$itemObj->TafsiliType2 = TAFTYPE_ACCOUNTS;
	$itemObj->TafsiliID2 = $AccountTafsili;
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
		$itemObj->details = "بابت پرداخت وام شماره " . $ReqObj->RequestID;
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
		$itemObj->TafsiliID = $ReqPersonTafsili;
		$itemObj->Add($pdo);
	}
	//---------- ردیف های تضمین  ----------
	
	$dt = PdoDataAccess::runquery("select * from DMS_documents 
		join BaseInfo b on(InfoID=DocType AND TypeID=8)
		join ACC_DocItems on(SourceType=" . DOCTYPE_DOCUMENT . " AND SourceID=DocumentID)
		where IsConfirm='YES' AND b.param1=1 AND ObjectType='loan' AND ObjectID=?", array($ReqObj->RequestID));
	$SumAmount = 0;
	$countAmount = 0;
	
	if(count($dt) == 0)
	{
		$dt = PdoDataAccess::runquery("
			SELECT d.DocumentID, dv.ParamValue, InfoDesc as DocTypeDesc,t.ParamValue as DocNo
				FROM DMS_DocParamValues dv
				join DMS_DocParams using(ParamID)
				join DMS_documents d using(DocumentID)
				join BaseInfo b on(InfoID=d.DocType AND TypeID=8)
				left join (
					select d.DocumentID,ParamValue
					from DMS_DocParamValues join DMS_DocParams using(ParamID)
					join DMS_documents d using(DocumentID)
					where Keytitle='no' and ObjectType='loan'
					group by DocumentID
				) t on(d.DocumentID=t.DocumentID)
				
			where IsConfirm='YES' AND b.param1=1 AND paramType='currencyfield' AND ObjectType='loan' AND ObjectID=?",
			array($ReqObj->RequestID), $pdo);
		
		foreach($dt as $row)
		{
			unset($itemObj->ItemID);
			unset($itemObj->TafsiliType2);
			unset($itemObj->TafsiliID2);
			$itemObj->CostID = $CostCode_guaranteeAmount_zemanati;
			$itemObj->DebtorAmount = $row["ParamValue"];
			$itemObj->CreditorAmount = 0;
			$itemObj->TafsiliType = TAFTYPE_PERSONS;
			$itemObj->TafsiliID = $LoanPersonTafsili;
			$itemObj->SourceType = DOCTYPE_DOCUMENT;
			$itemObj->SourceID = $row["DocumentID"];
			$itemObj->details = $row["DocTypeDesc"] . " به شماره " . $row["DocNo"];
			$itemObj->Add($pdo);
			
			$SumAmount += $row["ParamValue"]*1;
			$countAmount++;
		}
		if($SumAmount > 0)
		{
			unset($itemObj->ItemID);
			unset($itemObj->TafsiliType);
			unset($itemObj->TafsiliID);
			unset($itemObj->TafsiliType2);
			unset($itemObj->TafsiliID2);
			unset($itemObj->details);
			$itemObj->CostID = $CostCode_guaranteeAmount2_zemanati;
			$itemObj->DebtorAmount = 0;
			$itemObj->CreditorAmount = $SumAmount;	
			$itemObj->Add($pdo);

			/*unset($itemObj->ItemID);
			$itemObj->CostID = $CostCode_guaranteeCount;
			$itemObj->DebtorAmount = $countAmount;
			$itemObj->CreditorAmount = 0;	
			$itemObj->Add($pdo);

			unset($itemObj->ItemID);
			$itemObj->CostID = $CostCode_guaranteeCount2;
			$itemObj->DebtorAmount = 0;
			$itemObj->CreditorAmount = $countAmount;
			$itemObj->Add($pdo);*/
		}
	}
	//----------- cheques of installments -------------------------
	/*if($FirstStep)
	{		
		$dt = PdoDataAccess::runquery("
			SELECT PayAmount,BackPayID,ChequeNo
				FROM LON_BackPays
				where RequestID=? AND ChequeNo>0",	array($ReqObj->RequestID), $pdo);
		
		$SumAmount = 0;
		foreach($dt as $row)
		{
			unset($itemObj->ItemID);
			unset($itemObj->TafsiliType2);
			unset($itemObj->TafsiliID2);
			$itemObj->CostID = $CostCode_guaranteeAmount_daryafti;
			$itemObj->DebtorAmount = $row["PayAmount"];
			$itemObj->CreditorAmount = 0;
			$itemObj->TafsiliType = TAFTYPE_PERSONS;
			$itemObj->TafsiliID = $LoanPersonTafsili;
			$itemObj->SourceType = DOCTYPE_DOCUMENT;
			$itemObj->SourceID = $row["BackPayID"];
			$itemObj->details = "چک قسط به شماره " . $row["ChequeNo"];
			$itemObj->Add($pdo);
			
			$SumAmount += $row["PayAmount"]*1;
			$countAmount++;
		}
		if($SumAmount > 0)
		{
			unset($itemObj->ItemID);
			unset($itemObj->TafsiliType);
			unset($itemObj->TafsiliID);
			unset($itemObj->TafsiliType2);
			unset($itemObj->TafsiliID2);
			unset($itemObj->details);
			$itemObj->CostID = $CostCode_guaranteeAmount2_daryafti;
			$itemObj->DebtorAmount = 0;
			$itemObj->CreditorAmount = $SumAmount;	
			$itemObj->Add($pdo);
		}
	}*/
	//---------------------------------------------------------
	//------ ایجاد چک ------
	$chequeObj = new ACC_DocCheques();
	$chequeObj->DocID = $obj->DocID;
	$chequeObj->CheckDate = $PayObj->PayDate;
	$chequeObj->amount = $BankItemAmount;
	$chequeObj->TafsiliID = $LoanPersonTafsili;
	$chequeObj->description = " پرداخت وام شماره " . $ReqObj->RequestID;
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

function RegisterSHRTFUNDPayPartDoc($ReqObj, $PartObj, $PayObj, $BankTafsili, $AccountTafsili, $pdo, $DocID=""){
		
	
	/*@var $ReqObj LON_requests */
	/*@var $PartObj LON_ReqParts */
	/*@var $PayObj LON_payments */
	
	//------------- get CostCodes --------------------
	$LoanObj = new LON_loans($ReqObj->LoanID);
	$CostCode_Loan = FindCostID("110" . "-" . $LoanObj->_BlockCode);
	$CostCode_varizi = FindCostID("721-".$LoanObj->_BlockCode."-52");
	$CostCode_pardakhti = FindCostID("721-".$LoanObj->_BlockCode."-51");
	$CostCode_bank = FindCostID("101");
	$CostCode_todiee = FindCostID("200-".$LoanObj->_BlockCode."-01");
	$CostCode_agent_wage = FindCostID("200-02");
	
	$CostCode_guaranteeAmount_zemanati = FindCostID("904-02");
	$CostCode_guaranteeAmount2_zemanati = FindCostID("905-02");
	
	//------------------------------------------------
	
	$CycleID = substr(DateModules::miladi_to_shamsi($PayObj->PayDate), 0 , 4);
	
	//---------------- add doc header --------------------
	if($DocID == "")
	{
		$obj = new ACC_docs();
		$obj->RegDate = PDONOW;
		$obj->regPersonID = $_SESSION['USER']["PersonID"];
		$obj->DocDate = PDONOW;
		$obj->CycleID = $CycleID;
		$obj->BranchID = $ReqObj->BranchID;
		$obj->DocType = DOCTYPE_LOAN_PAYMENT;
		$obj->description = "پرداخت وام شماره " . $ReqObj->RequestID . " به نام " . 
			$ReqObj->_LoanPersonFullname;

		if(!$obj->Add($pdo))
		{
			ExceptionHandler::PushException("خطا در ایجاد سند");
			return false;
		}
	}
	else
		$obj = new ACC_docs($DocID);
	
	$PayAmount = $PayObj->PayAmount;
	//--------------------------------------------------------
	$payments = LON_payments::Get(" AND RequestID=? order by PayDate", array($PartObj->RequestID));
	$payments = $payments->fetchAll();
	//------------------ nfind tafsilis ---------------
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
			print_r(ExceptionHandler::PopAllExceptions());
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
	
	if($FirstStep)
	{
		unset($itemObj->ItemID);
		$itemObj->DocID = $obj->DocID;
		$itemObj->CostID = $CostCode_Loan;
		$itemObj->DebtorAmount = $PartObj->PartAmount;
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
		unset($itemObj->ItemID);
		$itemObj->DocID = $obj->DocID;
		$itemObj->CostID = $CostCode_todiee;
		$itemObj->DebtorAmount = $PayAmount;
		$itemObj->CreditorAmount = 0;
		$itemObj->Add($pdo);
	}
	//---------------------------------------------------------
	$itemObj = new ACC_DocItems();
	$itemObj->DocID = $obj->DocID;
	$itemObj->CostID = $CostCode_varizi;
	$itemObj->DebtorAmount = $PayAmount;
	$itemObj->CreditorAmount = 0;
	$itemObj->TafsiliType = TAFTYPE_PERSONS;
	$itemObj->TafsiliID = $ReqPersonTafsili;
	$itemObj->locked = "YES";
	$itemObj->SourceType = DOCTYPE_LOAN_PAYMENT;
	$itemObj->SourceID = $ReqObj->RequestID;
	$itemObj->SourceID2 = $PartObj->PartID;
	$itemObj->SourceID3 = $PayObj->PayID;
	$itemObj->Add($pdo);
	
	$itemObj = new ACC_DocItems();
	$itemObj->DocID = $obj->DocID;
	$itemObj->CostID = $CostCode_pardakhti;
	$itemObj->DebtorAmount = 0;
	$itemObj->CreditorAmount = $PayAmount;
	$itemObj->TafsiliType = TAFTYPE_PERSONS;
	$itemObj->TafsiliID = $ReqPersonTafsili;
	$itemObj->locked = "YES";
	$itemObj->SourceType = DOCTYPE_LOAN_PAYMENT;
	$itemObj->SourceID = $ReqObj->RequestID;
	$itemObj->SourceID2 = $PartObj->PartID;
	$itemObj->SourceID3 = $PayObj->PayID;
	$itemObj->Add($pdo);
	// ----------------------------- bank --------------------------------
	$AgentWage = 0;
	if($PartObj->CustomerWage*1 > $PartObj->FundWage*1 && $PartObj->AgentReturn == "CUSTOMER")
	{
		//$totalWage = ComputeWageOfSHekoofa($PartObj);
		$totalWage = $PayAmount*$PartObj->CustomerWage/100;
		$AgentFactor = ($PartObj->CustomerWage*1-$PartObj->FundWage*1)/$PartObj->CustomerWage*1;
		$AgentWage = $totalWage*$AgentFactor;		
	
		unset($itemObj->ItemID);
		$itemObj->CostID = $CostCode_agent_wage;
		$itemObj->DebtorAmount = 0;
		$itemObj->CreditorAmount = $AgentWage;
		$itemObj->TafsiliType = TAFTYPE_PERSONS;
		$itemObj->TafsiliID = $ReqPersonTafsili;
		unset($itemObj->TafsiliType2);
		unset($itemObj->TafsiliID2);
		$itemObj->Add($pdo);
	}
	
	$itemObj = new ACC_DocItems();
	$itemObj->DocID = $obj->DocID;
	$itemObj->CostID = $CostCode_bank;
	$itemObj->DebtorAmount = 0;
	$itemObj->CreditorAmount = $PayAmount - $AgentWage;
	$itemObj->TafsiliType = TAFTYPE_BANKS;
	$itemObj->TafsiliID = $BankTafsili;
	$itemObj->TafsiliType2 = TAFTYPE_ACCOUNTS;
	$itemObj->TafsiliID2 = $AccountTafsili;
	$itemObj->locked = "YES";
	$itemObj->SourceType = DOCTYPE_LOAN_PAYMENT;
	$itemObj->SourceID = $ReqObj->RequestID;
	$itemObj->SourceID2 = $PartObj->PartID;
	$itemObj->SourceID3 = $PayObj->PayID;
	$itemObj->Add($pdo);
	$BankRow = clone $itemObj;
	
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
			$itemObj->CostID = $CostCode_guaranteeAmount_zemanati;
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
		if($SumAmount > 0)
		{
			unset($itemObj->ItemID);
			unset($itemObj->TafsiliType);
			unset($itemObj->TafsiliID);
			unset($itemObj->TafsiliType2);
			unset($itemObj->TafsiliID2);
			unset($itemObj->details);
			$itemObj->CostID = $CostCode_guaranteeAmount2_zemanati;
			$itemObj->DebtorAmount = 0;
			$itemObj->CreditorAmount = $SumAmount;	
			$itemObj->Add($pdo);

			/*unset($itemObj->ItemID);
			$itemObj->CostID = $CostCode_guaranteeCount;
			$itemObj->DebtorAmount = $countAmount;
			$itemObj->CreditorAmount = 0;	
			$itemObj->Add($pdo);

			unset($itemObj->ItemID);
			$itemObj->CostID = $CostCode_guaranteeCount2;
			$itemObj->DebtorAmount = 0;
			$itemObj->CreditorAmount = $countAmount;
			$itemObj->Add($pdo);*/
		}
	}
	//--------------- cheques of installments ---------------------
	/*if($FirstStep)
	{
		$dt = PdoDataAccess::runquery("
			SELECT PayAmount,BackPayID
				FROM LON_BackPays
				where RequestID=? AND PayType=9",array($PartObj->RequestID), $pdo);
		
		$SumAmount = 0;
		foreach($dt as $row)
		{
			unset($itemObj->ItemID);
			unset($itemObj->TafsiliType2);
			unset($itemObj->TafsiliID2);
			$itemObj->CostID = $CostCode_guaranteeAmount_daryafti;
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
		if($SumAmount > 0)
		{
			unset($itemObj->ItemID);
			unset($itemObj->TafsiliType);
			unset($itemObj->TafsiliID);
			unset($itemObj->TafsiliType2);
			unset($itemObj->TafsiliID2);
			unset($itemObj->details);
			$itemObj->CostID = $CostCode_guaranteeAmount2_daryafti;
			$itemObj->DebtorAmount = 0;
			$itemObj->CreditorAmount = $SumAmount;	
			$itemObj->Add($pdo);

			
		}
	}*/
	//---------------------------------------------------------
	//------ ایجاد چک ------
	$chequeObj = new ACC_DocCheques();
	$chequeObj->DocID = $obj->DocID;
	$chequeObj->CheckDate = $PayObj->PayDate;
	$chequeObj->amount = $PartObj->PartAmount;
	$chequeObj->TafsiliID = $LoanPersonTafsili;
	$chequeObj->description = " پرداخت وام شماره " . $ReqObj->RequestID;
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

function ReturnPayPartDoc($DocID, $pdo, $DeleteDoc = true){
	
	PdoDataAccess::runquery("delete from ACC_DocItems where DocID=? AND locked='YES'", array($DocID));
	PdoDataAccess::runquery("delete from ACC_DocCheques where DocID=? ", array($DocID));

	if($DeleteDoc)
	{
		PdoDataAccess::runquery("delete d from ACC_docs d left join ACC_DocItems using(DocID)
		where DocID=? AND ItemID is null",	array($DocID), $pdo);
	}
	return ExceptionHandler::GetExceptionCount() == 0;
}

function RegisterLoanCost($CostObj, $CostID, $TafsiliID, $TafsiliID2, $pdo){
		
	//------------- get CostCodes --------------------
	$ReqObj = new LON_requests($CostObj->RequestID);
	$LoanObj = new LON_loans($ReqObj->LoanID);
	$CostCode_Loan = FindCostID("110" . "-" . $LoanObj->_BlockCode);
	//------------------------------------------------
	$CycleID = $_SESSION["accounting"]["CycleID"];
	//------------------- find load mode ---------------------
	$LoanMode = "";
	if(!empty($ReqObj->ReqPersonID))
	{
		$PersonObj = new BSC_persons($ReqObj->ReqPersonID);
		if($PersonObj->IsAgent == "YES")
			$LoanMode = "Agent";
	}
	else
		$LoanMode = "Customer";
	//------------------ find tafsilis ---------------
	$LoanPersonTafsili = FindTafsiliID($ReqObj->LoanPersonID, TAFTYPE_PERSONS);
	if(!$LoanPersonTafsili)
		return false;
	
	if($LoanMode == "Agent")
	{
		$ReqPersonTafsili = FindTafsiliID($ReqObj->ReqPersonID, TAFTYPE_PERSONS);
		if(!$ReqPersonTafsili)
			return false;
		$SubAgentTafsili = "";
		if(!empty($ReqObj->SubAgentID))
		{
			$SubAgentTafsili = FindTafsiliID($ReqObj->SubAgentID, TAFTYPE_SUBAGENT);
			if(!$SubAgentTafsili)
				return false;
		}
	}	
	//---------------- add doc header --------------------
	$obj = new ACC_docs();
	$obj->RegDate = PDONOW;
	$obj->regPersonID = $_SESSION['USER']["PersonID"];
	$obj->DocDate = PDONOW;
	$obj->CycleID = $CycleID;
	$obj->BranchID = $ReqObj->BranchID;
	$obj->DocType = DOCTYPE_LOAN_COST;
	$obj->description = "هزینه های مازاد وام شماره " . $ReqObj->RequestID . " به نام " . $ReqObj->_LoanPersonFullname;
	if(!$obj->Add($pdo))
	{
		ExceptionHandler::PushException("خطا در ایجاد سند");
		return false;
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
	$itemObj->SourceType = DOCTYPE_LOAN_COST;
	$itemObj->SourceID = $ReqObj->RequestID;
	$itemObj->SourceID2 = $CostObj->CostID;
	$itemObj->CostID = $CostCode_Loan;
	$itemObj->DebtorAmount = 0;
	$itemObj->CreditorAmount = $CostObj->CostAmount;
	$itemObj->Add($pdo);
	// ----------------------------- bank --------------------------------
	$CostCodeObj = new ACC_CostCodes($CostID);
	unset($itemObj->ItemID);
	unset($itemObj->TafsiliType);
	unset($itemObj->TafsiliType2);
	unset($itemObj->TafsiliID2);
	unset($itemObj->TafsiliID);
	$itemObj->locked = "NO";
	$itemObj->CostID = $CostID;
	$itemObj->DebtorAmount= $CostObj->CostAmount;
	$itemObj->CreditorAmount = 0;
	$itemObj->TafsiliType = $CostCodeObj->TafsiliType;
	if($TafsiliID != "")
		$itemObj->TafsiliID = $TafsiliID;
	$itemObj->TafsiliType2 = $CostCodeObj->TafsiliType2;
	if($TafsiliID2 != "")
		$itemObj->TafsiliID2 = $TafsiliID2;
	if(!$itemObj->Add($pdo))
	{
		ExceptionHandler::PushException("خطا در ایجاد ردیف بانک");
		return false;
	}		
	
	if(ExceptionHandler::GetExceptionCount() > 0)
		return false;
	
	return true;
}

//---------------------------------------------------------------

function RegisterDifferncePartsDoc($RequestID, $NewPartID, $pdo, $DocID=""){
	
	$ReqObj = new LON_requests($RequestID);
	$NewPartObj = new LON_ReqParts($NewPartID);
	
	$dt = PdoDataAccess::runquery("select * from LON_ReqParts 
		where RequestID=? AND IsHistory='YES' order by PartID desc limit 1", array($RequestID));
	$PreviousPartObj = new LON_ReqParts($dt[0]["PartID"]);
	
	//------------- get CostCodes --------------------
	$LoanObj = new LON_loans($ReqObj->LoanID);
	$CostCode_Loan = FindCostID("110" . "-" . $LoanObj->_BlockCode);
	$CostCode_wage = FindCostID("750" . "-" . $LoanObj->_BlockCode);
	$CostCode_FutureWage = FindCostID("760" . "-" . $LoanObj->_BlockCode);
	$CostCode_agent_wage = FindCostID("750" . "-52");
	$CostCode_agent_FutureWage = FindCostID("760" . "-52");
	$CostCode_todiee = FindCostID("200-".$LoanObj->_BlockCode."-01");
	//------------------------------------------------
	$CycleID = substr(DateModules::shNow(), 0 , 4);	
	//--------------------------------------------------
	$LoanMode = "";
	if(!empty($ReqObj->ReqPersonID))
	{
		$PersonObj = new BSC_persons($ReqObj->ReqPersonID);
		if($PersonObj->IsAgent == "YES")
			$LoanMode = "Agent";
	}
	else
		$LoanMode = "Customer";
	//------------------ find tafsilis ---------------
	$LoanPersonTafsili = FindTafsiliID($ReqObj->LoanPersonID, TAFTYPE_PERSONS);
	if(!$LoanPersonTafsili)
	{
		ExceptionHandler::PushException("تفصیلی مربوطه یافت نشد.[" . $ReqObj->LoanPersonID . "]");
		return false;
	}
	
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
	//------------------------------------------------
	// check for partAmount not greater than todiee
	if($NewPartObj->PartAmount*1 != $PreviousPartObj->PartAmount*1)
	{
		$dt = PdoDataAccess::runquery("select ifnull(sum(CreditorAmount-DebtorAmount),0) amount
			from ACC_DocItems where CostID=? AND TafsiliID=? AND sourceID=?",
			array($CostCode_todiee, $LoanPersonTafsili, $ReqObj->RequestID));
		
		if($PreviousPartObj->PartAmount*1 - $NewPartObj->PartAmount*1 > $dt[0]["amount"]*1)
		{
			ExceptionHandler::PushException("مبلغ جدید کمتر از مبلغی است که تاکنون پرداخت شده است ");
			return false;
		}
	}
	//---------------- add doc header --------------------
	if($DocID == "")
	{
		$obj = new ACC_docs();
		$obj->RegDate = PDONOW;
		$obj->regPersonID = $_SESSION['USER']["PersonID"];
		$obj->DocDate = PDONOW;
		$obj->CycleID = $CycleID;
		$obj->BranchID = $ReqObj->BranchID;
		$obj->DocType = DOCTYPE_LOAN_DIFFERENCE;
		$obj->description = "اختلاف حاصل از شرایط جدید برای وام شماره " . $ReqObj->RequestID;
		if(!$obj->Add($pdo))
		{
			ExceptionHandler::PushException("خطا در ایجاد سند");
			return false;
		}
	}
	else
	{
		$obj = new ACC_docs($DocID);
		PdoDataAccess::runquery("delete from ACC_DocItems where DocID=?", array($DocID), $pdo);
	}
	//--------------------------------------------------------
	// compute the previous amounts
	
	$diferences = array(
		"FundWageYears" => array(),
		"AgentWageYears" => array(),
		"CustomerYearDelays" => array()
	);
	
	$dt = PdoDataAccess::runquery("select * from LON_payments where RequestID=?", array($RequestID), $pdo);
	$firstPayDate = $dt[0]["PayDate"];
	$totalPaid = 0;
	foreach($dt as $PayRow)
	{
		$totalPaid += $PayRow["PayAmount"];
		$result_old = ComputeWagesAndDelays($PreviousPartObj, $PayRow["PayAmount"], $firstPayDate, $PayRow["PayDate"]);
		$result_new = ComputeWagesAndDelays($NewPartObj, $PayRow["PayAmount"], $firstPayDate, $PayRow["PayDate"]);

		foreach($result_new["FundWageYears"] as $year => $amount)
		{
			if(!isset($diferences["FundWageYears"][$year]))
				$diferences["FundWageYears"][$year] = 0;
			$diferences["FundWageYears"][$year] += $amount;
		}
		foreach($result_old["FundWageYears"] as $year => $amount)
		{
			if(!isset($diferences["FundWageYears"][$year]))
				$diferences["FundWageYears"][$year] = 0;
			$diferences["FundWageYears"][$year] -= $amount;
		}
		//.....................................................
		foreach($result_new["CustomerYearDelays"] as $year => $amount)
		{
			if(!isset($diferences["CustomerYearDelays"][$year]))
				$diferences["CustomerYearDelays"][$year] = 0;
			$diferences["CustomerYearDelays"][$year] += $amount;
		}
		foreach($result_old["CustomerYearDelays"] as $year => $amount)
		{
			if(!isset($diferences["CustomerYearDelays"][$year]))
				$diferences["CustomerYearDelays"][$year] = 0;
			$diferences["CustomerYearDelays"][$year] -= $amount;
		}
	}
	
	$MaxWage = max($NewPartObj->CustomerWage*1 , $NewPartObj->FundWage);
	$AgentFactor = $MaxWage == 0 ? 0 : ($NewPartObj->CustomerWage-$NewPartObj->FundWage)/$MaxWage;
	
	$result = ComputeWagesAndDelays($NewPartObj, $NewPartObj->PartAmount, $firstPayDate, $NewPartObj->PartDate);
	$TotalFundWage = $result["TotalFundWage"];
	$TotalAgentWage = $result["TotalAgentWage"];
	$TotalCustomerWage = $result["TotalCustomerWage"];
	$FundYears = $result["FundWageYears"];
	$AgentYears = $result["AgentWageYears"];
	
	$TotalFundDelay = $result["TotalFundDelay"];
	$TotalAgentDelay = $result["TotalAgentDelay"];
	$ExtraAmount = 0;
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
	$itemObj->SourceType = DOCTYPE_LOAN_DIFFERENCE;
	$itemObj->SourceID = $ReqObj->RequestID;
	$itemObj->SourceID2 = $NewPartObj->PartID;
	
	$extraAmount = 0;
	if($NewPartObj->WageReturn == "INSTALLMENT")
	{
		if($NewPartObj->MaxFundWage*1 > 0)
			$extraAmount += $NewPartObj->MaxFundWage;
		else if($NewPartObj->CustomerWage > $NewPartObj->FundWage)
			$extraAmount += $TotalFundWage;
		else
			$extraAmount += $TotalCustomerWage;
	}
		
	if($NewPartObj->AgentReturn == "INSTALLMENT" && $NewPartObj->CustomerWage>$NewPartObj->FundWage)
		$extraAmount += $TotalAgentWage;

	if($NewPartObj->DelayReturn == "INSTALLMENT")
		$extraAmount += $TotalFundDelay;
	if($TotalAgentDelay > 0 && $NewPartObj->AgentDelayReturn == "INSTALLMENT")
		$extraAmount += $TotalAgentDelay;
	
	function GetAmount($RequestID, $CostID, $TafsiliID = ""){
		
		$query = "select ifnull(sum(DebtorAmount-CreditorAmount),0) amount 
			from ACC_DocItems 
			where SourceType in(" . DOCTYPE_LOAN_PAYMENT . "," . DOCTYPE_LOAN_DIFFERENCE ." )
			AND SourceID=? AND CostID=?";
		$params = array($RequestID, $CostID);
		
		if($TafsiliID != "")
		{
			$query .= " AND TafsiliID=?";
			$params[] = $TafsiliID;
		}
		
		$dt = PdoDataAccess::runquery($query, $params);
		return $dt[0]["amount"]*1;
	}
	//...............Loan ..............................
	
	$CostCode_LoanAmount = GetAmount($RequestID, $CostCode_Loan) - ($NewPartObj->PartAmount*1 + $extraAmount);
	if($CostCode_LoanAmount != 0)
	{
		unset($itemObj->ItemID);
		$itemObj->DocID = $obj->DocID;
		$itemObj->CostID = $CostCode_Loan;
		$itemObj->DebtorAmount = $CostCode_LoanAmount < 0 ? abs($CostCode_LoanAmount) : 0;
		$itemObj->CreditorAmount = $CostCode_LoanAmount > 0 ? $CostCode_LoanAmount : 0;
		$itemObj->Add($pdo);
	}
	//.......... Todiee ....................................
	if($NewPartObj->PartAmount*1 != $PreviousPartObj->PartAmount)
	{
		$CostCode_todieeAmount = GetAmount($RequestID, $CostCode_todiee) - 
				($NewPartObj->PartAmount*1 - $totalPaid);
		unset($itemObj->ItemID);
		$itemObj->CostID = $CostCode_todiee;		
		$itemObj->DebtorAmount = $CostCode_todieeAmount < 0 ? abs($CostCode_todieeAmount) : 0;
		$itemObj->CreditorAmount = $CostCode_todieeAmount > 0 ? $CostCode_todieeAmount : 0;
		$itemObj->Add($pdo);
	}
	//------------------------ delay -------------------------------
	$curYear = substr(DateModules::miladi_to_shamsi($NewPartObj->PartDate), 0, 4)*1;
	$index = 0;
	foreach($diferences["CustomerYearDelays"] as $year => $value)
	{
		$FundYearAmount = ($NewPartObj->FundWage/$NewPartObj->DelayPercent)*$value;
		$AgentYearAmount = (($NewPartObj->CustomerWage*1-$NewPartObj->FundWage*1)/$NewPartObj->DelayPercent*1)*$value;
		if($FundYearAmount != 0)
		{
			unset($itemObj->ItemID);
			unset($itemObj->TafsiliType2);
			unset($itemObj->TafsiliID2);
			$itemObj->CostID = $year == $curYear ? $CostCode_wage : $CostCode_FutureWage;
			$itemObj->TafsiliType = TAFTYPE_YEARS;
			$itemObj->TafsiliID = FindTafsiliID($year, TAFTYPE_YEARS);
			$itemObj->DebtorAmount = $FundYearAmount<0 ? abs($FundYearAmount) : 0;
			$itemObj->CreditorAmount = $FundYearAmount>0 ? $FundYearAmount : 0;		
			$itemObj->details = "کارمزد دوره تنفس وام شماره " . $ReqObj->RequestID;
			if(!$itemObj->Add($pdo))
			{
				ExceptionHandler::PushException("خطا در ایجاد ردیف کارمزد تنفس");
				return false;
			}
			$ExtraAmount += $FundYearAmount>0 ? $FundYearAmount : 0;
		}
		if($AgentYearAmount > 0)
		{
			unset($itemObj->ItemID);
			$itemObj->CostID = $year == $curYear ? $CostCode_agent_wage : $CostCode_agent_FutureWage;
			$itemObj->DebtorAmount = $AgentYearAmount;
			$itemObj->CreditorAmount = 0;
			$itemObj->details = "اختلاف کارمزد تنفس وام شماره " . $ReqObj->RequestID;
			if(!$itemObj->Add($pdo))
			{
				ExceptionHandler::PushException("خطا در ایجاد ردیف کارمزد تنفس");
				return false;
			}
		}
		if($AgentYearAmount < 0)
		{
			unset($itemObj->ItemID);
			$itemObj->CostID = $year == $curYear ? $CostCode_agent_wage : $CostCode_agent_FutureWage;
			$itemObj->DebtorAmount = 0;
			$itemObj->CreditorAmount = $AgentYearAmount;
			$itemObj->details = "سهم کارمزد تنفس وام شماره " . $ReqObj->RequestID;
			if(!$itemObj->Add($pdo))
			{
				ExceptionHandler::PushException("خطا در ایجاد ردیف کارمزد تنفس");
				return false;
			}
		}
		$index++;
	}
	//------------------------ کارمزد---------------------	
	foreach($diferences["FundWageYears"] as $Year => $amount)
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
		$itemObj->CostID = $Year == $curYear ? $CostCode_wage : $CostCode_FutureWage;
		$itemObj->TafsiliType = TAFTYPE_YEARS;
		$itemObj->TafsiliID = $YearTafsili;
		$itemObj->details = "کارمزد وام شماره " . $ReqObj->RequestID;
		unset($itemObj->TafsiliType2);
		unset($itemObj->TafsiliID2);
		$itemObj->DebtorAmount = $amount<0 ? abs($amount) : 0;
		$itemObj->CreditorAmount = $amount>0 ? $amount : 0;
		$itemObj->Add($pdo);
		
		//$ExtraAmount += $amount<0 ? abs($amount) : 0;
		
		if($NewPartObj->AgentReturn == "INSTALLMENT")
		{
			if($LoanMode == "Agent" && $NewPartObj->FundWage*1 > $NewPartObj->CustomerWage)
			{
				unset($itemObj->ItemID);
				$itemObj->details = "اختلاف کارمزد وام شماره " . $ReqObj->RequestID;
				$itemObj->CostID = $Year == $curYear ? $CostCode_wage : $CostCode_FutureWage;
				$itemObj->DebtorAmount = $amount*$AgentFactor<0 ? abs($amount*$AgentFactor) : 0;
				$itemObj->CreditorAmount = $amount*$AgentFactor>0 ? $amount*$AgentFactor : 0;
				$itemObj->TafsiliType = TAFTYPE_YEARS;
				$itemObj->TafsiliID = $YearTafsili;
				$itemObj->TafsiliType2 = TAFTYPE_PERSONS;
				$itemObj->TafsiliID2 = $ReqPersonTafsili;
				$itemObj->Add($pdo);
			}
			if($LoanMode == "Agent" && $NewPartObj->FundWage*1 < $NewPartObj->CustomerWage)
			{
				unset($itemObj->ItemID);
				$itemObj->details = "سهم کارمزد وام شماره " . $ReqObj->RequestID;
				$itemObj->CostID = $Year == $curYear ? $CostCode_wage : $CostCode_FutureWage;
				$itemObj->DebtorAmount = $amount*$AgentFactor>0 ? $amount*$AgentFactor : 0;
				$itemObj->CreditorAmount = $amount*$AgentFactor<0 ? abs($amount*$AgentFactor) : 0;			
				$itemObj->TafsiliType = TAFTYPE_YEARS;
				$itemObj->TafsiliID = $YearTafsili;
				$itemObj->TafsiliType2 = TAFTYPE_PERSONS;
				$itemObj->TafsiliID2 = $ReqPersonTafsili;
				$itemObj->Add($pdo);
			}
		}
	}
	
	/*if($NewPartObj->AgentReturn == "CUSTOMER" && $NewPartObj->CustomerWage > $NewPartObj->FundWage)
	{
		unset($itemObj->ItemID);
		$itemObj->details = "سهم کارمزد وام شماره " . $ReqObj->RequestID;
		$itemObj->CostID = $CostCode_deposite;
		$itemObj->DebtorAmount = 0;
		$itemObj->CreditorAmount = $TotalAgentWage;
		$itemObj->TafsiliType = TAFTYPE_PERSONS;
		$itemObj->TafsiliID = $ReqPersonTafsili;
		if($LoanMode == "Agent")
		{
			$itemObj->TafsiliType2 = TAFTYPE_PERSONS;
			$itemObj->TafsiliID2 = $ReqPersonTafsili;
		}
		$itemObj->Add($pdo);
	}*/
	// ---------------------------- ExtraAmount --------------------------------
	if($ExtraAmount > 0)
	{
		$itemObj = new ACC_DocItems();
		$itemObj->DocID = $obj->DocID;
		$itemObj->CostID = $CostCode_Loan;
		$itemObj->DebtorAmount = $ExtraAmount;
		$itemObj->CreditorAmount = 0;	
		$itemObj->locked = "YES";
		$itemObj->SourceID = $ReqObj->RequestID;
		$itemObj->SourceID2 = $NewPartObj->PartID;
		$itemObj->Add($pdo);
		
		//-------------------- add loan cost for difference --------------------
		$dt = PdoDataAccess::runquery("select * from LON_costs where RequestID=? AND PartID=?", 
				array($NewPartObj->RequestID, $NewPartObj->PartID));
		if(count($dt)>0)
		{
			$obj = new LON_costs($dt[0]["CostID"]);
			$obj->CostAmount = $ExtraAmount;
			$obj->Edit($pdo);
		}
		else
		{
			$obj = new LON_costs();
			$obj->RequestID = $NewPartObj->RequestID;
			$obj->CostDesc = "اختلافت حاصل از تغییر شرایط پرداخت";
			$obj->CostAmount = $ExtraAmount;
			$obj->CostID = $CostCode_Loan;
			$obj->IsPartDiff = "YES";
			$obj->PartID = $NewPartObj->PartID;
			$obj->Add($pdo);		
		}
	}
	//---------------------------------------------------------
	if(ExceptionHandler::GetExceptionCount() > 0)
		return false;
	
	return true;
}
//---------------------------------------------------------------

function RegisterCustomerPayDoc($DocObj, $PayObj, $CostID, $TafsiliID, $TafsiliID2, 
		$CenterAccount, $BranchID, $FirstCostID, $SecondCostID, $pdo, $grouping=false){
	
	/*@var $PayObj LON_BackPays */
	$ReqObj = new LON_requests($PayObj->RequestID);
	$PartObj = LON_ReqParts::GetValidPartObj($PayObj->RequestID);
	if($DocObj == null)
	{
		$dt = PdoDataAccess::runquery("select * from ACC_DocItems where SourceType=" . 
			DOCTYPE_INSTALLMENT_PAYMENT . " AND SourceID=? AND SourceID2=?" , 
			array($ReqObj->RequestID, $PayObj->BackPayID));
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
	$CostCode_wage = FindCostID("750" . "-" . $LoanObj->_BlockCode);
	$CostCode_commitment = FindCostID("200-" . $LoanObj->_BlockCode . "-51");
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
		if($grouping)
			$obj->description = "پرداخت گروهی اقساط";
		else
			$obj->description = "پرداخت قسط وام شماره " . 
				$ReqObj->RequestID . " به نام " . $ReqObj->_LoanPersonFullname;

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
	//--------------------------------------------------------
	
	$firstPayDate = $PartObj->PartDate;
	$result = ComputeWagesAndDelays($PartObj, $PartObj->PartAmount, $firstPayDate, $PartObj->PartDate);
	$TotalFundWage = $result["TotalFundWage"];
	
	$TotalFundDelay = $result["TotalFundDelay"];
	$TotalAgentDelay = $result["TotalAgentDelay"];
	
	$PayObj->PayAmount = $PayObj->PayAmount*1;
	$curWage = round($PayObj->PayAmount*$TotalFundWage/$PartObj->PartAmount);
	
	//----------------- get total remain ---------------------
	require_once getenv("DOCUMENT_ROOT") . '/loan/request/request.class.php';
	$dt = array();
	$returnArr = LON_requests::ComputePayments($PayObj->RequestID, $dt, $pdo);
	$ExtraPay = 0;
	if($returnArr[ count($returnArr)-1 ]["TotalRemainder"]*1 < 0)
	{
		$PayObj->PayAmount = $PayObj->PayAmount + $returnArr[ count($returnArr)-1 ]["TotalRemainder"]*1 ;
		$PayObj->Edit();
		
		$ExtraPay = $returnArr[ count($returnArr)-1 ]["TotalRemainder"]*-1;
		$PayObj->PayAmount = $PayObj->PayAmount;
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
	
	//---------------- loan -----------------
	$itemObj->DocID = $obj->DocID;
	$itemObj->CostID = $CostCode_Loan;
	$itemObj->DebtorAmount = 0;
	$itemObj->CreditorAmount = $PayObj->PayAmount;
	if(!$itemObj->Add($pdo))
	{
		ExceptionHandler::PushException("خطا در ایجاد ردیف وام");
		return false;
	}
	// -------------- bank ---------------
	if($CenterAccount)
	{
		unset($itemObj->ItemID);
		unset($itemObj->TafsiliType);
		unset($itemObj->TafsiliType2);
		unset($itemObj->TafsiliID2);
		unset($itemObj->TafsiliID);
		$itemObj->locked = "NO";
		$itemObj->CostID = $FirstCostID;
		$itemObj->DebtorAmount= $PayObj->PayAmount + $ExtraPay;
		$itemObj->CreditorAmount = 0;
		if(!$itemObj->Add($pdo))
		{
			ExceptionHandler::PushException("خطا در ایجاد ردیف محل پرداخت");
			return false;
		}
		
		$Secobj = new ACC_docs();
		$Secobj->RegDate = PDONOW;
		$Secobj->regPersonID = $_SESSION['USER']["PersonID"];
		$Secobj->DocDate = PDONOW;
		$Secobj->CycleID = $CycleID;
		$Secobj->BranchID = $BranchID;
		$Secobj->DocType = DOCTYPE_INSTALLMENT_PAYMENT;
		$Secobj->description = "پرداخت قسط وام شماره " . 
			$ReqObj->RequestID . " به نام " . $ReqObj->_LoanPersonFullname;

		if(!$Secobj->Add($pdo))
		{
			ExceptionHandler::PushException("خطا در ایجاد سند شعبه واسط ");
			return false;
		}
		unset($itemObj->ItemID);
		unset($itemObj->TafsiliType);
		unset($itemObj->TafsiliType2);
		unset($itemObj->TafsiliID2);
		unset($itemObj->TafsiliID);
		$itemObj->DocID = $Secobj->DocID;
		$itemObj->CostID = $SecondCostID;
		$itemObj->DebtorAmount= 0;
		$itemObj->CreditorAmount = $PayObj->PayAmount + $ExtraPay;
		if(!$itemObj->Add($pdo))
		{
			ExceptionHandler::PushException("خطا در ایجاد ردیف سند شعبه واسط");
			return false;
		}
		
		$CostObj = new ACC_CostCodes($CostID);
		unset($itemObj->ItemID);
		unset($itemObj->TafsiliType);
		unset($itemObj->TafsiliType2);
		unset($itemObj->TafsiliID2);
		unset($itemObj->TafsiliID);
		$itemObj->CostID = $CostID;
		$itemObj->DebtorAmount= $PayObj->PayAmount + $ExtraPay;
		$itemObj->CreditorAmount = 0;
		$itemObj->TafsiliType = $CostObj->TafsiliType;
		if($TafsiliID != "")
			$itemObj->TafsiliID = $TafsiliID;
		$itemObj->TafsiliType2 = $CostObj->TafsiliType2;
		if($TafsiliID2 != "")
			$itemObj->TafsiliID2 = $TafsiliID2;
		if(!$itemObj->Add($pdo))
		{
			ExceptionHandler::PushException("خطا در ایجاد ردیف سند شعبه واسط");
			return false;
		}		
	}
	else
	{		
		$CostObj = new ACC_CostCodes($CostID);
		unset($itemObj->ItemID);
		unset($itemObj->TafsiliType);
		unset($itemObj->TafsiliType2);
		unset($itemObj->TafsiliID2);
		unset($itemObj->TafsiliID);
		$itemObj->locked = "NO";
		$itemObj->CostID = $CostID;
		$itemObj->DebtorAmount= $PayObj->PayAmount + $ExtraPay;
		$itemObj->CreditorAmount = 0;
		$itemObj->TafsiliType = $CostObj->TafsiliType;
		if($TafsiliID != "")
			$itemObj->TafsiliID = $TafsiliID;
		$itemObj->TafsiliType2 = $CostObj->TafsiliType2;
		if($TafsiliID2 != "")
			$itemObj->TafsiliID2 = $TafsiliID2;

		if(!$itemObj->Add($pdo))
		{
			ExceptionHandler::PushException("خطا در ایجاد ردیف بانک");
			return false;
		}		
	}
	$itemObj->locked = "YES";
	$itemObj->DocID = $obj->DocID;
	unset($itemObj->TafsiliType2);
	unset($itemObj->TafsiliID2);
	//-------------- extra to Pasandaz ----------------
	if($ExtraPay > 0)
	{
		unset($itemObj->ItemID);
		$itemObj->DocID = $obj->DocID;
		$itemObj->CostID = COSTID_saving;
		$itemObj->DebtorAmount = 0;
		$itemObj->CreditorAmount = $ExtraPay;
		$itemObj->TafsiliType = TAFTYPE_PERSONS;
		$itemObj->TafsiliID = $LoanPersonTafsili;
		if(!$itemObj->Add($pdo))
		{
			ExceptionHandler::PushException("خطا در ایجاد ردیف وام");
			return false;
		}
	}
	//----------------------------------------
	if($PartObj->DelayReturn == "INSTALLMENT")
	{
		$dt = PdoDataAccess::runquery("select ifnull(sum(CreditorAmount),0) from ACC_DocItems 
			where CostID=? AND SourceID=? AND SourceID3=?",
			array($CostCode_wage, $ReqObj->RequestID, "1"));
		$TotalFundDelay = $TotalFundDelay*1 - $dt[0][0]*1;
		
		unset($itemObj->ItemID);
		unset($itemObj->TafsiliType);
		unset($itemObj->TafsiliID);
		unset($itemObj->TafsiliType2);
		unset($itemObj->TafsiliID2);
		$itemObj->CostID = $CostCode_wage;
		$itemObj->details = "بابت تنفس وام " . $PayObj->RequestID;
		$itemObj->DebtorAmount = 0;
		$itemObj->SourceID3 = "1";
		$itemObj->CreditorAmount = min($TotalFundDelay , $PayObj->PayAmount);
		if(!$itemObj->Add($pdo))
		{
			ExceptionHandler::PushException("خطا در ایجاد سند");
			return false;
		}
		$PayObj->PayAmount = $PayObj->PayAmount - min($TotalFundDelay , $PayObj->PayAmount);
		unset($itemObj->SourceID3);
	}
	if($PayObj->PayAmount > 0 && $LoanMode == "Agent" && $PartObj->AgentDelayReturn == "INSTALLMENT")
	{
		$dt = PdoDataAccess::runquery("select ifnull(sum(CreditorAmount),0) from ACC_DocItems 
			where CostID=? AND SourceID=? AND SourceID2=? AND SourceID3=?",
			array($CostCode_deposite, $ReqObj->RequestID, $PayObj->BackPayID, "1"));
		
		$TotalAgentDelay = $TotalAgentDelay*1 - $dt[0][0]*1;
		
		unset($itemObj->ItemID);
		unset($itemObj->TafsiliType);
		unset($itemObj->TafsiliID);
		unset($itemObj->TafsiliType2);
		unset($itemObj->TafsiliID2);
		$itemObj->CostID = $CostCode_deposite;
		$itemObj->DebtorAmount = 0;
		$itemObj->details = "بابت تنفس وام " . $PayObj->RequestID;
		$itemObj->SourceID3 = "1";
		$itemObj->CreditorAmount = min($TotalAgentDelay , $PayObj->PayAmount);
		if(!$itemObj->Add($pdo))
		{
			ExceptionHandler::PushException("خطا در ایجاد سند");
			return false;
		}
		$PayObj->PayAmount = $PayObj->PayAmount - min($TotalAgentDelay , $PayObj->PayAmount);
		unset($itemObj->SourceID3);
	}
	//------------- wage --------------
	if($LoanMode == "Agent")
	{
		if($PayObj->PayAmount*1 > 0)
		{
			$amount = $PayObj->PayAmount;
			if($PartObj->WageReturn == "INSTALLMENT")
			{
				$amount = $PayObj->PayAmount - $curWage;
				$amount = $amount < 0 ? 0 : $amount;
			}

			//---- اضافه به سپرده -----
			unset($itemObj->ItemID);
			unset($itemObj->TafsiliType2);
			unset($itemObj->TafsiliID2);
			$itemObj->details = "پرداخت قسط وام شماره " . $ReqObj->RequestID ;
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
				ExceptionHandler::PushException("خطا در ایجاد ردیف سپرده");
				return false;
			}

			unset($itemObj->ItemID);
			$itemObj->CostID = $CostCode_commitment;
			$itemObj->DebtorAmount = $amount;
			$itemObj->CreditorAmount = 0;
			if(!$itemObj->Add($pdo))
			{
				ExceptionHandler::PushException("خطا در ایجاد ردیف تعهد");
				return false;
			}
		}
	}
	
	//---------------------------------------------------------
	if(ExceptionHandler::GetExceptionCount() > 0)
		return false;
		
	return true;
}

function RegisterSHRTFUNDCustomerPayDoc($DocObj, $PayObj, $CostID, $TafsiliID, $TafsiliID2, 
		$CenterAccount, $BranchID, $FirstCostID, $SecondCostID, $pdo, $grouping=false){
	
	/*@var $PayObj LON_BackPays */
	$ReqObj = new LON_requests($PayObj->RequestID);
	
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
	$CostCode_bank = FindCostID("101");
	$CostCode_centerAccount = FindCostID("499");
	$CostCode_varizi = FindCostID("721-".$LoanObj->_BlockCode."-52");
	$CostCode_pardakhti = FindCostID("721-".$LoanObj->_BlockCode."-51");
	
	$CostCode_guaranteeAmount_zemanati = FindCostID("904-02");
	$CostCode_guaranteeAmount_daryafti = FindCostID("904-04");
	$CostCode_guaranteeAmount2_zemanati = FindCostID("905-02");
	$CostCode_guaranteeAmount2_daryafti = FindCostID("905-04");
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
		if($grouping)
			$obj->description = "پرداخت گروهی اقساط";
		else
			$obj->description = "پرداخت قسط وام شماره " . 
				$ReqObj->RequestID . " به نام " . $ReqObj->_LoanPersonFullname;

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
	
	//----------------- add Doc items ------------------------
		
	$itemObj = new ACC_DocItems();
	$itemObj->DocID = $obj->DocID;
	$itemObj->details = "پرداخت قسط وام شماره " . $ReqObj->RequestID ;
	$itemObj->TafsiliType = TAFTYPE_PERSONS;
	$itemObj->TafsiliID = $LoanPersonTafsili;
	$itemObj->TafsiliType2 = TAFTYPE_PERSONS;
	$itemObj->TafsiliID2 = $ReqPersonTafsili;
	$itemObj->locked = "YES";
	$itemObj->SourceType = DOCTYPE_INSTALLMENT_PAYMENT;
	$itemObj->SourceID = $ReqObj->RequestID;
	$itemObj->SourceID2 = $PayObj->BackPayID;
	
	//---------------- loan -----------------
	$itemObj->DocID = $obj->DocID;
	$itemObj->CostID = $CostCode_Loan;
	$itemObj->DebtorAmount = 0;
	$itemObj->CreditorAmount = $PayObj->PayAmount;
	if(!$itemObj->Add($pdo))
	{
		ExceptionHandler::PushException("خطا در ایجاد سند");
		return false;
	}
	
	//------------ varizi ----------------
	unset($itemObj->ItemID);
	unset($itemObj->TafsiliType2);
	unset($itemObj->TafsiliID2);
	$itemObj->CostID = $CostCode_varizi;
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

	//--------------- pardakhtani -----------
	unset($itemObj->ItemID);
	$itemObj->CostID = $CostCode_pardakhti;
	$itemObj->DebtorAmount = $PayObj->PayAmount;
	$itemObj->CreditorAmount = 0;
	if(!$itemObj->Add($pdo))
	{
		ExceptionHandler::PushException("خطا در ایجاد سند");
		return false;
	}

	// -------------- bank ---------------
	if($CenterAccount)
	{
		unset($itemObj->ItemID);
		unset($itemObj->TafsiliType);
		unset($itemObj->TafsiliType2);
		unset($itemObj->TafsiliID2);
		unset($itemObj->TafsiliID);
		$itemObj->locked = "NO";
		$itemObj->CostID = $FirstCostID;
		$itemObj->DebtorAmount= $PayObj->PayAmount;
		$itemObj->CreditorAmount = 0;
		if(!$itemObj->Add($pdo))
		{
			ExceptionHandler::PushException("خطا در ایجاد سند");
			return false;
		}
		
		$Secobj = new ACC_docs();
		$Secobj->RegDate = PDONOW;
		$Secobj->regPersonID = $_SESSION['USER']["PersonID"];
		$Secobj->DocDate = PDONOW;
		$Secobj->CycleID = $CycleID;
		$Secobj->BranchID = $BranchID;
		$Secobj->DocType = DOCTYPE_INSTALLMENT_PAYMENT;
		$Secobj->description = "پرداخت قسط وام شماره " . 
				$ReqObj->RequestID . " به نام " . $ReqObj->_LoanPersonFullname;

		if(!$Secobj->Add($pdo))
		{
			ExceptionHandler::PushException("خطا در ایجاد سند");
			return false;
		}
		unset($itemObj->ItemID);
		unset($itemObj->TafsiliType);
		unset($itemObj->TafsiliType2);
		unset($itemObj->TafsiliID2);
		unset($itemObj->TafsiliID);
		$itemObj->DocID = $Secobj->DocID;
		$itemObj->CostID = $SecondCostID;
		$itemObj->DebtorAmount= 0;
		$itemObj->CreditorAmount = $PayObj->PayAmount;
		if(!$itemObj->Add($pdo))
		{
			ExceptionHandler::PushException("خطا در ایجاد سند");
			return false;
		}
		
		$CostObj = new ACC_CostCodes($CostID);
		unset($itemObj->ItemID);
		unset($itemObj->TafsiliType);
		unset($itemObj->TafsiliType2);
		unset($itemObj->TafsiliID2);
		unset($itemObj->TafsiliID);
		$itemObj->CostID = $CostID;
		$itemObj->DebtorAmount= $PayObj->PayAmount;
		$itemObj->CreditorAmount = 0;
		$itemObj->TafsiliType = $CostObj->TafsiliType;
		if($TafsiliID != "")
			$itemObj->TafsiliID = $TafsiliID;
		$itemObj->TafsiliType2 = $CostObj->TafsiliType2;
		if($TafsiliID2 != "")
			$itemObj->TafsiliID2 = $TafsiliID2;
		if(!$itemObj->Add($pdo))
		{
			ExceptionHandler::PushException("خطا در ایجاد سند");
			return false;
		}
	}
	else
	{
		$CostObj = new ACC_CostCodes($CostID);
		unset($itemObj->ItemID);
		unset($itemObj->TafsiliType);
		unset($itemObj->TafsiliType2);
		unset($itemObj->TafsiliID2);
		unset($itemObj->TafsiliID);
		$itemObj->locked = "NO";
		$itemObj->CostID = $PayObj->PayType == "3" ? $CostCode_fund : $CostCode_bank;
		$itemObj->DebtorAmount= $PayObj->PayAmount;
		$itemObj->CreditorAmount = 0;
		$itemObj->TafsiliType = $CostObj->TafsiliType;
		if($TafsiliID != "")
			$itemObj->TafsiliID = $TafsiliID;
		$itemObj->TafsiliType2 = $CostObj->TafsiliType2;
		if($TafsiliID2 != "")
			$itemObj->TafsiliID2 = $TafsiliID2;
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
		array($PayObj->RequestID, $PayObj->BackPayID), $pdo);
	if(count($dt) == 0)
		return true;
	
	PdoDataAccess::runquery("delete from ACC_DocItems 
		where SourceType=" . DOCTYPE_INSTALLMENT_PAYMENT . " AND SourceID=? AND SourceID2=?",
		array($PayObj->RequestID, $PayObj->BackPayID), $pdo);
	
	if(!$EditMode)
	{
		PdoDataAccess::runquery("delete d from ACC_docs d left join ACC_DocItems using(DocID)
		where ItemID is null AND DocID=?",	array($dt[0][0]), $pdo);
	}
	
	return true;
	
	//return ACC_docs::Remove($dt[0][0], $pdo);
}
//---------------------------------------------------------------

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
		$CostCode_guaranteeAmount_zemanati = FindCostID("904-02");
		$CostCode_guaranteeAmount_daryafti = FindCostID("904-04");
		$CostCode_guaranteeAmount2_zemanati = FindCostID("905-02");
		$CostCode_guaranteeAmount2_daryafti = FindCostID("905-04");
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
			$itemObj->CostID = $CostCode_guaranteeAmount_zemanati;
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
		if($SumAmount > 0)
		{
			unset($itemObj->ItemID);
			unset($itemObj->TafsiliType);
			unset($itemObj->TafsiliID);
			unset($itemObj->TafsiliType2);
			unset($itemObj->TafsiliID2);
			unset($itemObj->details);
			$itemObj->CostID = $CostCode_guaranteeAmount2_zemanati;
			$itemObj->DebtorAmount = $SumAmount;
			$itemObj->CreditorAmount = 0;	
			$itemObj->Add($pdo);

			/*unset($itemObj->ItemID);
			$itemObj->CostID = $CostCode_guaranteeCount;
			$itemObj->DebtorAmount = 0;
			$itemObj->CreditorAmount = count($dt) + count($dt2);	
			$itemObj->Add($pdo);

			unset($itemObj->ItemID);
			$itemObj->CostID = $CostCode_guaranteeCount2;
			$itemObj->DebtorAmount = count($dt) + count($dt2);
			$itemObj->CreditorAmount = 0;
			$itemObj->Add($pdo);*/
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
function RegisterOuterCheque($DocID, $InChequeObj, $pdo, $CostID ="", $TafsiliID="", $TafsiliID2="", 
		$CenterAccount="", $BranchID="", $FirstCostID="", $SecondCostID=""){

	/*@var $InChequeObj ACC_IncomeCheques */
	
	$CycleID = substr(DateModules::shNow(), 0 , 4);
	
	//------------- get CostCodes --------------------
	$CostCode_guaranteeAmount_daryafti = FindCostID("904-04");
	$CostCode_guaranteeAmount2_daryafti = FindCostID("905-04");
	//-------------------- BranchID -----------------------
	$BackPays = $InChequeObj->GetBackPays($pdo);
	if(count($BackPays) > 0)
		$FirstBranchID = $BackPays[0]["BranchID"];
	else
		$FirstBranchID = $_SESSION["accounting"]["BranchID"];
	//---------------- add doc header --------------------
	if($DocID == "")
	{		
		$obj = new ACC_docs();
		$obj->RegDate = PDONOW;
		$obj->regPersonID = $_SESSION['USER']["PersonID"];
		$obj->DocDate = PDONOW;
		$obj->CycleID = $CycleID;
		$obj->BranchID = $FirstBranchID;
		$obj->DocType = DOCTYPE_INCOMERCHEQUE;
		$obj->description = "چک شماره " . $InChequeObj->ChequeNo;
		if(!$obj->Add($pdo))
		{
			ExceptionHandler::PushException("خطا در ایجاد سند");
			return false;
		}
	}
	else
		$obj = new ACC_docs($DocID, $pdo);
	//----------------- add Doc items ------------------------
	
	$__ChequeAmount = $InChequeObj->ChequeAmount;
	$__ChequeID = $InChequeObj->IncomeChequeID;
	$__TafsiliID = $InChequeObj->TafsiliID;
	if($__TafsiliID == "")
	{
		$dt = $InChequeObj->GetBackPays();
		if(count($dt) == 1)
			$__TafsiliID = $dt[0]["TafsiliID"];
	}
	$__SourceType = DOCTYPE_INCOMERCHEQUE;
	
	$itemObj = new ACC_DocItems();
	$itemObj->DocID = $obj->DocID;
	$itemObj->locked = "YES";
	$itemObj->TafsiliType = TAFTYPE_PERSONS;
	$itemObj->TafsiliID = $__TafsiliID;
	$itemObj->TafsiliType2 = TAFTYPE_ChequeStatus;
	$itemObj->TafsiliID2 = $InChequeObj->ChequeStatus;
	$itemObj->SourceType = $__SourceType;
	$itemObj->SourceID = $__ChequeID;
	$itemObj->details = "چک شماره " . $InChequeObj->ChequeNo;
	
	//............................................................
	
	if($InChequeObj->ChequeStatus == INCOMECHEQUE_NOTVOSUL)
	{ 
		unset($itemObj->ItemID);
		$itemObj->CostID = $CostCode_guaranteeAmount_daryafti;
		$itemObj->DebtorAmount = $__ChequeAmount;
		$itemObj->CreditorAmount = 0;		
		
		$itemObj->Add($pdo);

		unset($itemObj->ItemID);
		$itemObj->CostID = $CostCode_guaranteeAmount2_daryafti;
		$itemObj->DebtorAmount = 0;
		$itemObj->CreditorAmount = $__ChequeAmount;
		$itemObj->Add($pdo);		
		
		if(ExceptionHandler::GetExceptionCount() > 0)
			return false;

		return $obj->DocID;
	}
	//............................................................
	
	if($InChequeObj->ChequeStatus == INCOMECHEQUE_VOSUL)
	{
		unset($itemObj->ItemID);
		$itemObj->CostID = $CostCode_guaranteeAmount2_daryafti;
		$itemObj->DebtorAmount = $__ChequeAmount;
		$itemObj->CreditorAmount = 0;
		$itemObj->Add($pdo);

		unset($itemObj->ItemID);
		$itemObj->CostID = $CostCode_guaranteeAmount_daryafti;
		$itemObj->DebtorAmount = 0;
		$itemObj->CreditorAmount = $__ChequeAmount;
		$itemObj->Add($pdo);
	
		if(count($BackPays) > 0)
		{
			foreach($BackPays as $row)
			{
				$BackPayObj = new LON_BackPays($row["BackPayID"]);
				$ReqObj = new LON_requests($BackPayObj->RequestID);
				$PersonObj = new BSC_persons($ReqObj->ReqPersonID);
				if($PersonObj->IsSupporter == "YES")
					$result = RegisterSHRTFUNDCustomerPayDoc($obj,$BackPayObj,$CostID, $TafsiliID, $TafsiliID2, 
					$CenterAccount, $BranchID, $FirstCostID, $SecondCostID, $pdo);
				else
					$result = RegisterCustomerPayDoc($obj,$BackPayObj,$CostID, $TafsiliID, $TafsiliID2, 
					$CenterAccount, $BranchID, $FirstCostID, $SecondCostID, $pdo);
			}
		}
		else
		{
			if($CenterAccount == "true")
			{
				unset($itemObj->ItemID);
				unset($itemObj->TafsiliType);
				unset($itemObj->TafsiliType2);
				unset($itemObj->TafsiliID2);
				unset($itemObj->TafsiliID);
				$itemObj->locked = "NO";
				$itemObj->CostID = $FirstCostID;
				$itemObj->DebtorAmount= $__ChequeAmount;
				$itemObj->CreditorAmount = 0;
				if(!$itemObj->Add($pdo))
				{
					ExceptionHandler::PushException("خطا در ایجاد سند");
					return false;
				}

				$Secobj = new ACC_docs();
				$Secobj->RegDate = PDONOW;
				$Secobj->regPersonID = $_SESSION['USER']["PersonID"];
				$Secobj->DocDate = PDONOW;
				$Secobj->CycleID = $CycleID;
				$Secobj->BranchID = $BranchID;
				$Secobj->DocType = $__SourceType;
				if(!$Secobj->Add($pdo))
				{
					ExceptionHandler::PushException("خطا در ایجاد سند");
					return false;
				}
				unset($itemObj->ItemID);
				unset($itemObj->TafsiliType);
				unset($itemObj->TafsiliType2);
				unset($itemObj->TafsiliID2);
				unset($itemObj->TafsiliID);
				$itemObj->DocID = $Secobj->DocID;
				$itemObj->CostID = $SecondCostID;
				$itemObj->DebtorAmount= 0;
				$itemObj->CreditorAmount = $__ChequeAmount;
				if(!$itemObj->Add($pdo))
				{
					ExceptionHandler::PushException("خطا در ایجاد سند");
					return false;
				}
				unset($itemObj->ItemID);
				unset($itemObj->TafsiliType);
				unset($itemObj->TafsiliType2);
				unset($itemObj->TafsiliID2);
				unset($itemObj->TafsiliID);
				$CostObj = new ACC_CostCodes($CostID);
				$itemObj->CostID = $CostID;
				$itemObj->DebtorAmount= $__ChequeAmount;
				$itemObj->CreditorAmount = 0;
				$itemObj->TafsiliType = $CostObj->TafsiliType;
				if($TafsiliID != "")
					$itemObj->TafsiliID = $TafsiliID;
				$itemObj->TafsiliType2 = $CostObj->TafsiliType2;
				if($TafsiliID2 != "")
					$itemObj->TafsiliID2 = $TafsiliID2;
				if(!$itemObj->Add($pdo))
				{
					ExceptionHandler::PushException("خطا در ایجاد سند");
					return false;
				}
			}
			else
			{
				unset($itemObj->ItemID);
				unset($itemObj->TafsiliType);
				unset($itemObj->TafsiliType2);
				unset($itemObj->TafsiliID2);
				unset($itemObj->TafsiliID);
				$itemObj->locked = "NO";
				$CostObj = new ACC_CostCodes($CostID);
				$itemObj->CostID = $CostID;
				$itemObj->DebtorAmount= $__ChequeAmount;
				$itemObj->CreditorAmount = 0;
				$itemObj->TafsiliType = $CostObj->TafsiliType;
				if($TafsiliID != "")
					$itemObj->TafsiliID = $TafsiliID;
				$itemObj->TafsiliType2 = $CostObj->TafsiliType2;
				if($TafsiliID2 != "")
					$itemObj->TafsiliID2 = $TafsiliID2;
				if(!$itemObj->Add($pdo))
				{
					ExceptionHandler::PushException("خطا در ایجاد سند");
					return false;
				}
			}
			
			unset($itemObj->ItemID);
			$itemObj->locked = "YES";
			$itemObj->DocID = $obj->DocID;
			$itemObj->CostID = $InChequeObj->CostID;
			$itemObj->DebtorAmount = 0;
			$itemObj->CreditorAmount = $__ChequeAmount;
			$itemObj->TafsiliType = $InChequeObj->TafsiliType;
			$itemObj->TafsiliID = $InChequeObj->TafsiliID;
			$itemObj->TafsiliType2 = $InChequeObj->TafsiliType2;
			$itemObj->TafsiliID2 = $InChequeObj->TafsiliID2;
			if(!$itemObj->Add($pdo))
			{
				ExceptionHandler::PushException("خطا در ایجاد سند");
				return false;
			}
		}
		
		if(ExceptionHandler::GetExceptionCount() > 0)
			return false;

		return true;
	}
	//............................................................

	if(array_search($InChequeObj->ChequeStatus, array(INCOMECHEQUE_EBTAL,INCOMECHEQUE_MOSTARAD,
			INCOMECHEQUE_BARGHASHTI_MOSTARAD,INCOMECHEQUE_MAKHDOOSH,INCOMECHEQUE_CHANGE)) !== false)
	{
		$itemObj->DocID = $obj->DocID;
		$itemObj->CostID = $CostCode_guaranteeAmount_daryafti;
		$itemObj->CreditorAmount = $__ChequeAmount;
		$itemObj->DebtorAmount = 0;
		$itemObj->TafsiliType = TAFTYPE_PERSONS;
		$itemObj->TafsiliID = $__TafsiliID;
		$itemObj->SourceType = $__SourceType;
		$itemObj->SourceID = $__ChequeID;
		$itemObj->details = "چک شماره " . $InChequeObj->ChequeNo;
		$itemObj->Add($pdo);

		unset($itemObj->ItemID);
		$itemObj->CostID = $CostCode_guaranteeAmount2_daryafti;
		$itemObj->CreditorAmount = 0;
		$itemObj->DebtorAmount = $__ChequeAmount;
		$itemObj->Add($pdo);
	
		//---------------------------------------------------------
		if(ExceptionHandler::GetExceptionCount() > 0)
			return false;

		return true;
	}
	
	return true;
}

//---------------------------------------------------------------

function ComputeDepositeProfit($ToDate, $Tafsilis, $ReportMode = false){
	
	//-------------------get percents ---------------------
	$dt = PdoDataAccess::runquery("select * from ACC_cycles where CycleID=" . 
			$_SESSION["accounting"]["CycleID"]);
	$DepositePercents = array(
		COSTID_ShortDeposite => $dt[0]["ShortDepositPercent"],
		COSTID_LongDeposite  => $dt[0]["LongDepositPercent"]
	);
	//-----------------------------------------------------
	$DepositeAmount = array(
		COSTID_ShortDeposite => array(),
		COSTID_LongDeposite => array()
	);
	$TraceArr = array();
	foreach($Tafsilis as $TafsiliID)
	{
		//-------------- get latest deposite compute -------------
		$FirstYearDay = DateModules::shamsi_to_miladi($_SESSION["accounting"]["CycleID"] . "-01-01", "-");
		$dt = PdoDataAccess::runquery("select DocID,DocDate,SourceID
			from ACC_docs where DocType=" . DOCTYPE_DEPOSIT_PROFIT . " 
				AND CycleID=" . $_SESSION["accounting"]["CycleID"] . "
				AND BranchID=" . $_SESSION["accounting"]["BranchID"] . "	
				AND TafsiliID=?
			order by DocID desc", array($TafsiliID));

		$LatestComputeDate = count($dt)==0 ? $FirstYearDay : $dt[0]["SourceID"];
		$LatestComputeDocID = count($dt)==0 ? 0 : $dt[0]["DocID"];

		//----------- check for all docs confirm --------------
		/*$dt = PdoDataAccess::runquery("select group_concat(distinct LocalNo) from ACC_docs 
			join ACC_DocItems using(DocID)
			where DocID>=? AND CostID in(" . COSTID_ShortDeposite . "," . COSTID_LongDeposite . ")
			AND DocStatus not in('CONFIRM','ARCHIVE')
			AND TafsiliID=?", array($LatestComputeDocID,$TafsiliID));
		if(count($dt) > 0 && $dt[0][0] != "")
		{
			echo Response::createObjectiveResponse(false, "اسناد با شماره های [" . $dt[0][0] . "] تایید نشده اند و قادر به صدور سند سود سپرده نمی باشید.");
			die();
		}*/
		//------------ get sum of deposites ----------------
		$dt = PdoDataAccess::runquery("select TafsiliID,CostID,sum(CreditorAmount-DebtorAmount) amount
			from ACC_DocItems join ACC_docs using(DocID)
			where DocID<=? 
				AND CostID in(" . COSTID_ShortDeposite . "," . COSTID_LongDeposite . ")
				AND CycleID=" . $_SESSION["accounting"]["CycleID"] . "
				AND BranchID=" . $_SESSION["accounting"]["BranchID"] . "
				AND TafsiliID=?
			group by TafsiliID,CostID", array($LatestComputeDocID,$TafsiliID));

		foreach($dt as $row)
		{
			$DepositeAmount[ $row["CostID"] ][ $row["TafsiliID"] ]["amount"] = $row["amount"];
			$DepositeAmount[ $row["CostID"] ][ $row["TafsiliID"] ]["lastDate"] = $LatestComputeDate;
			$TraceArr[ $row["TafsiliID"] ][] = array(
				"row" => $row,
				"date" => $LatestComputeDate,
				"profit" => 0
			);
		}
		
		//------------ get the Deposite amount -------------
		$dt = PdoDataAccess::runquery("
			select CostID,TafsiliID,DocDate,sum(CreditorAmount-DebtorAmount) amount
			from ACC_DocItems 
				join ACC_docs using(DocID)
			where CostID in(" . COSTID_ShortDeposite . "," . COSTID_LongDeposite . ")
				AND DocID > ?
				AND TafsiliID=?
				AND CycleID=" . $_SESSION["accounting"]["CycleID"] . "
				AND BranchID=" . $_SESSION["accounting"]["BranchID"] . "
			group by DocDate", 
				array($LatestComputeDocID, $TafsiliID));

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
				$DepositePercents[ $row["CostID"] ]/(36500);

			if(!isset($DepositeAmount[ $row["CostID"] ][ $row["TafsiliID"] ]["profit"]))
				$DepositeAmount[ $row["CostID"] ][ $row["TafsiliID"] ]["profit"] = 0;
			$DepositeAmount[ $row["CostID"] ][ $row["TafsiliID"] ]["profit"] += $amount;

			//echo $row["TafsiliID"] ."@" . $DepositeAmount[ $row["CostID"] ][ $row["TafsiliID"] ]["profit"] . "\n";

			$DepositeAmount[ $row["CostID"] ][ $row["TafsiliID"] ]["amount"] += $row["amount"];
			$DepositeAmount[ $row["CostID"] ][ $row["TafsiliID"] ]["lastDate"] = $row["DocDate"];	
			
			$TraceArr[ $row["TafsiliID"] ][] = array(
				"row" => $row,
				"date" => $lastDate,
				"days" => $days,
				"profit" => $amount
			);
		}
		//--------------------- compute untill toDate ------------------------------
		foreach($DepositeAmount[ COSTID_ShortDeposite ] as $tafsili => &$row)
		{
			$days = DateModules::GDateMinusGDate($ToDate, $row["lastDate"]);
			$amount = $row["amount"] * $days * $DepositePercents[ COSTID_ShortDeposite ]/(100*30.5);

			if(!isset($row["profit"]))
				$row["profit"] = 0;
			$row["profit"] += $amount;

			$TraceArr[ $tafsili ][] = array(
				"row" => $row,
				"days" => $days,
				"profit" => $amount
			);
			//echo $tafsili ."@" . $DepositeAmount[ COSTID_ShortDeposite ][ $tafsili ]["profit"] . "\n";
		}
		foreach($DepositeAmount[ COSTID_LongDeposite ] as $tafsili => &$row)
		{
			$days = DateModules::GDateMinusGDate($ToDate, $row["lastDate"]);
			$amount = $row["amount"] * $days * $DepositePercents[ COSTID_LongDeposite ]/(36500);

			if(!isset($row["profit"]))
				$row["profit"] = 0;
			$row["profit"] += $amount;

			$TraceArr[ $tafsili ][] = array(
				"row" => $row,
				"date" => $row["lastDate"],
				"days" => $days,
				"profit" => $amount
			);
			//echo $tafsili ."@" . $DepositeAmount[ COSTID_ShortDeposite ][ $tafsili ]["profit"] . "\n";
		}
	}
	if($ReportMode)
		return $TraceArr;
	//--------------------------------------------------------------------------
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
	$obj->description = "محاسبه سود سپرده تا تاریخ " . DateModules::miladi_to_shamsi($ToDate);
	
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
			$itemObj->SourceID = $ToDate;
			if(!$itemObj->Add($pdo))
			{
				print_r(ExceptionHandler::PopAllExceptions());
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

function RegisterWarrantyDoc($ReqObj, $WageCost, $BankTafsili, $AccountTafsili,$Block_CostID, $DocID, $pdo){
	
	/*@var $ReqObj WAR_requests */
	
	//------------- get CostCodes --------------------
	$CostCode_warrenty = FindCostID("300");
	$CostCode_warrenty_commitment = FindCostID("700");
	$CostCode_wage = FindCostID("750-07");
	$CostCode_FutureWage = FindCostID("760-07");
	$CostCode_fund = FindCostID("100");
	$CostCode_seporde = FindCostID("690");
	$CostCode_pasandaz = FindCostID("209-10");
	
	$CostCode_guaranteeAmount_zemanati = FindCostID("904-02");
	$CostCode_guaranteeAmount_daryafti = FindCostID("904-04");
	$CostCode_guaranteeAmount2_zemanati = FindCostID("905-02");
	$CostCode_guaranteeAmount2_daryafti = FindCostID("905-04");
	//------------------------------------------------
	$CycleID = substr(DateModules::miladi_to_shamsi($ReqObj->StartDate), 0 , 4);
	//------------------ find tafsilis ---------------
	$PersonTafsili = FindTafsiliID($ReqObj->PersonID, TAFTYPE_PERSONS);
	if(!$PersonTafsili)
	{
		ExceptionHandler::PushException("تفصیلی مربوطه یافت نشد.[" . $ReqObj->PersonID . "]");
		return false;
	}
	
	//------------------- compute wage ------------------
	$days = DateModules::GDateMinusGDate($ReqObj->EndDate,$ReqObj->StartDate);
	//if(DateModules::YearIsLeap($CycleID));
		$days -= 1;
	$TotalWage = round($days*$ReqObj->amount*0.9*$ReqObj->wage/36500) + $ReqObj->RegisterAmount*1;	
	
	$years = SplitYears(DateModules::miladi_to_shamsi($ReqObj->StartDate), 
		DateModules::miladi_to_shamsi($ReqObj->EndDate), $TotalWage);
	
	//--------------- check pasandaz remaindar -----------------
	
	$dt = PdoDataAccess::runquery("select sum(CreditorAmount-DebtorAmount) remain
		from ACC_DocItems join ACC_docs using(DocID) where CycleID=? AND CostID=?
			AND TafsiliType=? AND TafsiliID=? AND BranchID=?", array(
				$CycleID,
				$CostCode_pasandaz,
				TAFTYPE_PERSONS,
				$PersonTafsili,
				$ReqObj->BranchID
			));
	if($WageCost == "209-10" && $dt[0][0]*1 < $ReqObj->amount*0.1)
	{
		ExceptionHandler::PushException("مانده حساب پس انداز مشتری کمتر از 10% مبلغ ضمانت نامه می باشد");
		return false;
	}
	if($WageCost == "209-10" && $dt[0][0]*1 < ($ReqObj->amount*0.1 + $TotalWage))
	{
		ExceptionHandler::PushException("مانده حساب پس انداز مشتری کمتر از مبلغ کارمزد می باشد");
		return false;
	}
	if($ReqObj->IsBlock == "YES")
	{
		if($Block_CostID != "" && $Block_CostID != $CostCode_pasandaz)
		{
			$dt = PdoDataAccess::runquery("select sum(CreditorAmount-DebtorAmount) remain
			from ACC_DocItems join ACC_docs using(DocID) where CycleID=? AND CostID=?
				AND TafsiliType=? AND TafsiliID=? AND BranchID=?", array(
					$CycleID,
					$Block_CostID,
					TAFTYPE_PERSONS,
					$PersonTafsili,
					$ReqObj->BranchID
				));
		}
		$amount = $ReqObj->amount*1;
		if($WageCost == "209-10")
			$amount += $ReqObj->amount*0.1 + $TotalWage;
		
		if($dt[0][0]*1 < $amount)
		{
			ExceptionHandler::PushException("مانده حساب انتخابی جهت بلوکه کمتر از مبلغ ضمانت نامه می باشد");
			return false;
		}
	}
	
	//---------------- add doc header --------------------
	if($DocID == null)
	{
		$DocObj = new ACC_docs();
		$DocObj->RegDate = PDONOW;
		$DocObj->regPersonID = $_SESSION['USER']["PersonID"];
		$DocObj->DocDate = PDONOW;
		$DocObj->CycleID = $CycleID;
		$DocObj->BranchID = $ReqObj->BranchID;
		$DocObj->DocType = DOCTYPE_WARRENTY;
		$DocObj->description = "ضمانت نامه " . $ReqObj->_TypeDesc . " به شماره " . 
				$ReqObj->RequestID . " به نام " . $ReqObj->_fullname;

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
	
	//---------------------------- block Cost ----------------------------
	if($ReqObj->IsBlock == "YES")
	{
		$blockObj = new ACC_CostBlocks();
		$blockObj->CostID = !empty($Block_CostID) ? $Block_CostID : $CostCode_pasandaz;
		$blockObj->TafsiliType = TAFTYPE_PERSONS;
		$blockObj->TafsiliID = $PersonTafsili;
		$blockObj->BlockAmount = $ReqObj->amount;
		$blockObj->IsLock = "YES";
		$blockObj->SourceType = DOCTYPE_WARRENTY;
		$blockObj->SourceID = $ReqObj->RequestID;
		$blockObj->details = "بابت ضمانت نامه شماره " . $ReqObj->RequestID;
		if(!$blockObj->Add())
		{
			print_r(ExceptionHandler::PopAllExceptions());
			ExceptionHandler::PushException("خطا در بلوکه کردن حساب پس انداز");
			return false;
		}
	}
	// ---------------------- Warrenty costs -----------------------------
	$totalCostAmount = 0;
	$dt = PdoDataAccess::runquery("select * from WAR_costs where RequestID=?", array($ReqObj->RequestID));
	foreach($dt as $row)
	{
		$totalCostAmount += ($row["CostType"] == "DEBTOR"? 1 : -1)*$row["CostAmount"]*1;
		
		unset($itemObj->ItemID);
		$itemObj->SourceID2 = $row["CostID"];
		$itemObj->details = $row["CostDesc"];
		$itemObj->CostID = $row["CostCodeID"];
		$itemObj->DebtorAmount = $row["CostType"] == "DEBTOR" ? $row["CostAmount"] : 0;
		$itemObj->CreditorAmount = $row["CostType"] == "CREDITOR" ? $row["CostAmount"] : 0;
		$itemObj->Add($pdo);
	}
	// ----------------------------- bank --------------------------------
	unset($itemObj->ItemID);
	unset($itemObj->TafsiliType);
	unset($itemObj->TafsiliID);
	$itemObj->details = "بابت 10% سپرده ضمانت نامه شماره " . $ReqObj->RequestID;
	$itemObj->CostID = $CostCode_seporde;
	$itemObj->DebtorAmount = 0;
	$itemObj->CreditorAmount = $ReqObj->amount*0.1;
	$itemObj->Add($pdo);
		
	unset($itemObj->ItemID);
	$itemObj->details = "بابت کارمزد ضمانت نامه شماره " . $ReqObj->RequestID;
	$itemObj->CostID = FindCostID($WageCost);
	$itemObj->DebtorAmount = $TotalWage + $ReqObj->amount*0.1 - $totalCostAmount;
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
		$itemObj->CostID = $CostCode_guaranteeAmount_zemanati;
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
		$itemObj->CostID = $CostCode_guaranteeAmount2_zemanati;
		$itemObj->DebtorAmount = 0;
		$itemObj->CreditorAmount = $SumAmount;	
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
	
	PdoDataAccess::runquery("delete from ACC_CostBlocks 
			where SourceType=" . DOCTYPE_WARRENTY . " AND SourceID=?",
			array($ReqObj->RequestID), $pdo);
	
	if($EditMode)
	{
		PdoDataAccess::runquery("delete from ACC_DocItems 
			where SourceType=" . DOCTYPE_WARRENTY . " AND SourceID=? AND SourceID2=?",
			array($ReqObj->RequestID, $ReqObj->ReqVersion), $pdo);
		return true;
	}
	
	return ACC_docs::Remove($dt[0][0], $pdo);
}

function EndWarrantyDoc($ReqObj, $pdo){
	
	/*@var $ReqObj WAR_requests */
	
	//------------- get CostCodes --------------------
	$CostCode_warrenty = FindCostID("300");
	$CostCode_warrenty_commitment = FindCostID("700");
	$CostCode_wage = FindCostID("750-07");
	$CostCode_FutureWage = FindCostID("760-07");
	$CostCode_fund = FindCostID("100");
	$CostCode_seporde = FindCostID("690");
	$CostCode_pasandaz = FindCostID("209-10");
	
	$CostCode_guaranteeAmount_zemanati = FindCostID("904-02");
	$CostCode_guaranteeAmount_daryafti = FindCostID("904-04");
	$CostCode_guaranteeAmount2_zemanati = FindCostID("905-02");
	$CostCode_guaranteeAmount2_daryafti = FindCostID("905-04");
	//------------------------------------------------
	$CycleID = substr(DateModules::miladi_to_shamsi($ReqObj->StartDate), 0 , 4);
	//------------------ find tafsilis ---------------
	$PersonTafsili = FindTafsiliID($ReqObj->PersonID, TAFTYPE_PERSONS);
	if(!$PersonTafsili)
	{
		ExceptionHandler::PushException("تفصیلی مربوطه یافت نشد.[" . $ReqObj->PersonID . "]");
		return false;
	}
	//---------------- add doc header --------------------
	$DocObj = new ACC_docs();
	$DocObj->RegDate = PDONOW;
	$DocObj->regPersonID = $_SESSION['USER']["PersonID"];
	$DocObj->DocDate = PDONOW;
	$DocObj->CycleID = $CycleID;
	$DocObj->BranchID = $ReqObj->BranchID;
	$DocObj->DocType = DOCTYPE_WARRENTY_END;
	$DocObj->description = "خاتمه ضمانت نامه " . $ReqObj->_TypeDesc . " به شماره " . 
			$ReqObj->RequestID . " به نام " . $ReqObj->_fullname;

	if(!$DocObj->Add($pdo))
	{
		ExceptionHandler::PushException("خطا در ایجاد سند");
		return false;
	}
	//----------------- add Doc items ------------------------
	$itemObj = new ACC_DocItems();
	$itemObj->DocID = $DocObj->DocID;
	$itemObj->TafsiliType = TAFTYPE_PERSONS;
	$itemObj->TafsiliID = $PersonTafsili;
	$itemObj->SourceType = DOCTYPE_WARRENTY_END;
	$itemObj->SourceID = $ReqObj->RequestID;
	$itemObj->SourceID2 = $ReqObj->ReqVersion;
	$itemObj->locked = "YES";
	
	$itemObj->CostID = $CostCode_warrenty;
	$itemObj->CreditorAmount = $ReqObj->amount;
	$itemObj->DebtorAmount = 0;
	if(!$itemObj->Add($pdo))
	{
		ExceptionHandler::PushException("خطا در ثبت ردیف ضمانت نامه");
		return false;
	}
	
	unset($itemObj->ItemID);
	$itemObj->CostID = $CostCode_warrenty_commitment;
	$itemObj->CreditorAmount = 0;
	$itemObj->DebtorAmount = $ReqObj->amount;
	if(!$itemObj->Add($pdo))
	{
		ExceptionHandler::PushException("خطا در ثبت ردیف تعهد ضمانت نامه");
		return false;
	}
	//---------------------------- block Cost ----------------------------
	if($ReqObj->IsBlock == "YES")
	{
		$dt = PdoDataAccess::runquery("select * from ACC_blocks where SourceType=? AND SourceID=?",
				array(DOCTYPE_WARRENTY, $ReqObj->RequestID));
		if(count($dt) > 0)
		{
			$blockObj = new ACC_CostBlocks($dt[0]["BlockID"]);
			$blockObj->IsActive = "NO";
			$blockObj->Edit($pdo);
		}
	}
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
		$itemObj->CostID = $CostCode_guaranteeAmount_zemanati;
		$itemObj->DebtorAmount = 0;
		$itemObj->CreditorAmount = $row["ParamValue"];
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
		$itemObj->CostID = $CostCode_guaranteeAmount2_zemanati;
		$itemObj->DebtorAmount = $SumAmount;
		$itemObj->CreditorAmount = 0;	
		$itemObj->Add($pdo);
	}
	
	if(ExceptionHandler::GetExceptionCount() > 0)
		return false;
	
	return $DocObj->DocID;
}

//---------------------------------------------------------------

?>
