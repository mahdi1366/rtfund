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
	
	if($TafsiliType == TAFTYPE_PERSONS)
		$dt = PdoDataAccess::runquery("select * from ACC_tafsilis "
			. "where IsActive='YES' AND ObjectID=? AND TafsiliType=?",
		array($TafsiliCode, $TafsiliType));
	else
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
		
		/*$query = "select ifnull(sum(CreditorAmount-DebtorAmount),0),group_concat(LocalNo) docs
			from ACC_DocItems join ACC_docs using(DocID) 
			where CostID=? AND TafsiliID=? AND sourceID=?";
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
				. number_format($dt[0][0]) . "\n ریال می باشد که کمتر از مبلغ این مرحله از پرداخت وام می باشد"
				. "<br>\n [ شماره اسناد : " . $dt[0]["docs"] . " ]");
			return false;
		}*/
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
		
		if($PartObj->PartAmount != $PayAmount)
		{
			unset($itemObj->ItemID);
			$itemObj->DocID = $obj->DocID;
			$itemObj->CostID = $CostCode_todiee;
			$itemObj->TafsiliType = TAFTYPE_PERSONS;
			$itemObj->TafsiliID = $ReqPersonTafsili;
			unset($itemObj->TafsiliType2);
			unset($itemObj->TafsiliID2);
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
		}
		
		unset($itemObj->ItemID);
		$itemObj->DocID = $obj->DocID;
		$itemObj->CostID = $CostCode_todiee;
		$itemObj->DebtorAmount = $PayAmount;
		$itemObj->CreditorAmount = 0;
		$itemObj->TafsiliType = TAFTYPE_PERSONS;
		$itemObj->TafsiliID = $ReqPersonTafsili;
		unset($itemObj->TafsiliType2);
		unset($itemObj->TafsiliID2);
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
			unset($itemObj->TafsiliType2);
			unset($itemObj->TafsiliID2);
			
			if($PartObj->AgentDelayReturn == "INSTALLMENT")
				$itemObj->CostID = $CostCode_agent_wage;
			else
				$itemObj->CostID = $CostCode_deposite;
			
			$itemObj->TafsiliType = TAFTYPE_PERSONS;
			$itemObj->TafsiliID = $ReqPersonTafsili;
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
			unset($itemObj->TafsiliType2);
			unset($itemObj->TafsiliID2);
			if($PartObj->AgentDelayReturn == "INSTALLMENT")
				$itemObj->CostID = $CostCode_agent_wage;
			else
				$itemObj->CostID = $CostCode_deposite;
			$itemObj->DebtorAmount = 0;
			$itemObj->CreditorAmount = $totalAgentYearAmount;
			$itemObj->TafsiliType = TAFTYPE_PERSONS;
			$itemObj->TafsiliID = $ReqPersonTafsili;
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
			$itemObj->CostID = $Year == $curYear ? $CostCode_wage : $CostCode_FutureWage;
			$itemObj->DebtorAmount = 0;
			$itemObj->CreditorAmount = $amount;
			$itemObj->TafsiliType = TAFTYPE_YEARS;
			$itemObj->TafsiliID = $YearTafsili;
			if($LoanMode == "Agent")
			{
				$itemObj->TafsiliType2 = TAFTYPE_PERSONS;
				$itemObj->TafsiliID2 = $ReqPersonTafsili;
			}
			$itemObj->Add($pdo);
			
			if($PartObj->FundWage*1 > $PartObj->CustomerWage)
			{
				unset($itemObj->ItemID);
				$itemObj->details = "اختلاف کارمزد وام شماره " . $ReqObj->RequestID;
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
	if($dt[0]["dsum"] != $dt[0]["csum"])
	{
		$BankRow->CreditorAmount += $dt[0]["dsum"] - $dt[0]["csum"];
		$BankRow->Edit($pdo);
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
	$CostCode_FundComit_mande = FindCostID("721-".$LoanObj->_BlockCode."-52-02");
	$CostCode_CustomerComit = FindCostID("721-".$LoanObj->_BlockCode."-51");
	$CostCode_bank = FindCostID("101");
	$CostCode_todiee = FindCostID("200-".$LoanObj->_BlockCode."-01");
	$CostCode_FundComit_wage = FindCostID("721-".$LoanObj->_BlockCode."-52-03");
	
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
	$payments = LON_payments::Get(" AND RequestID=?", array($PartObj->RequestID), " order by PayDate");
	$payments = $payments->fetchAll();
	//------------------ nfind tafsilis ---------------
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
	
	//------------ find the number step to pay ---------------
	$FirstStep = true;
	$dt = PdoDataAccess::runquery("select * from ACC_DocItems 
		where SourceType='".DOCTYPE_LOAN_PAYMENT."' AND SourceID=? AND SourceID2=?", 
			array($ReqObj->RequestID, $PartObj->PartID));
	if(count($dt) > 0)
	{
		$FirstStep = false;
		/*$query = "select ifnull(sum(CreditorAmount-DebtorAmount),0)
			from ACC_DocItems where CostID=? AND TafsiliID=? AND sourceID=? AND TafsiliID2=?";
		$param = array($CostCode_todiee, $LoanPersonTafsili, $ReqObj->RequestID, $ReqPersonTafsili);
	
		$dt = PdoDataAccess::runquery($query, $param);
		if($dt[0][0]*1 < $PayAmount)
		{
			ExceptionHandler::PushException("حساب تودیعی این مشتری"
					. number_format($dt[0][0]) . " ریال می باشد که کمتر از مبلغ این مرحله از پرداخت وام می باشد");
			return false;
		}*/
	}
	//---------------- compute wage --------------------------
	$InstallmentWage = 0;
	if($PartObj->AgentReturn == "INSTALLMENT")
		$InstallmentWage = ComputeWageOfSHekoofa($PartObj);		

	//----------------- add Doc items ------------------------
	$itemObj = new ACC_DocItems();
	$itemObj->DocID = $obj->DocID;
	$itemObj->TafsiliType = TAFTYPE_PERSONS;
	$itemObj->TafsiliID = $LoanPersonTafsili;
	$itemObj->TafsiliType2 = TAFTYPE_PERSONS;
	$itemObj->TafsiliID2 = $ReqPersonTafsili;
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
		$itemObj->DebtorAmount = $PartObj->PartAmount*1 + $InstallmentWage;
		$itemObj->CreditorAmount = 0;
		$itemObj->Add($pdo);
		$LoanRow = clone $itemObj;
		
		if($InstallmentWage > 0)
		{
			unset($itemObj->ItemID);
			$itemObj->CostID = $CostCode_CustomerComit;
			$itemObj->DebtorAmount = 0;
			$itemObj->CreditorAmount = $InstallmentWage;
			$itemObj->TafsiliType = TAFTYPE_PERSONS;
			$itemObj->TafsiliID = $ReqPersonTafsili;
			$itemObj->Add($pdo);
		}
		
		if($PartObj->PartAmount != $PayAmount)
		{
			unset($itemObj->ItemID);
			$itemObj->DocID = $obj->DocID;
			$itemObj->CostID = $CostCode_todiee;
			$itemObj->TafsiliType = TAFTYPE_PERSONS;
			$itemObj->TafsiliID = $ReqPersonTafsili;
			unset($itemObj->TafsiliType2);
			unset($itemObj->TafsiliID2);
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
		$itemObj->TafsiliType = TAFTYPE_PERSONS;
		$itemObj->TafsiliID = $ReqPersonTafsili;
		unset($itemObj->TafsiliType2);
		unset($itemObj->TafsiliID2);
		$itemObj->DebtorAmount = $PayAmount;
		$itemObj->CreditorAmount = 0;
		$itemObj->Add($pdo);
	}
	//---------------------------------------------------------
	$itemObj = new ACC_DocItems();
	$itemObj->DocID = $obj->DocID;
	$itemObj->CostID = $CostCode_FundComit_mande;
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
	$itemObj->CostID = $CostCode_CustomerComit;
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
		$totalWage = $PayAmount*$PartObj->CustomerWage/100;
		$AgentFactor = ($PartObj->CustomerWage*1-$PartObj->FundWage*1)/$PartObj->CustomerWage*1;
		$AgentWage = $totalWage*$AgentFactor;		
	
		unset($itemObj->ItemID);
		$itemObj->CostID = $CostCode_FundComit_wage;
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
	$itemObj->DebtorAmount = $CostObj->CostAmount;
	$itemObj->CreditorAmount = 0;
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
	$itemObj->DebtorAmount= 0;
	$itemObj->CreditorAmount = $CostObj->CostAmount;
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
	
	$ReqPersonObj = new BSC_persons($ReqObj->ReqPersonID);
	if($ReqPersonObj->IsSupporter == "YES")
		return RegisterDifferncePartsDoc_Supporter($ReqObj, $NewPartObj, $pdo, $DocID);
	
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
	$CostCode_deposite = FindCostID("210-01");
	$CostCode_todiee = FindCostID("200-". $LoanObj->_BlockCode."-01");
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
		"CustomerYearDelays" => array(),
		"TotalFundWage" => 0,
		"TotalAgentWage" => 0,
		"TotalCustomerWage" => 0,
		"TotalFundDelay" => 0,
		"TotalAgentDelay" => 0
	);
	
	$dt = PdoDataAccess::runquery("select * from LON_payments where RequestID=?", array($RequestID), $pdo);
	if(count($dt) == 0)
	{
		ExceptionHandler::PushException("هیچ ﭘرداختی برای این وام ثبت نشده است");
		return false;
	}
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
		foreach($result_new["AgentWageYears"] as $year => $amount)
		{
			if(!isset($diferences["AgentWageYears"][$year]))
				$diferences["AgentWageYears"][$year] = 0;
			$diferences["AgentWageYears"][$year] += $amount;
		}
		foreach($result_old["AgentWageYears"] as $year => $amount)
		{
			if(!isset($diferences["AgentWageYears"][$year]))
				$diferences["AgentWageYears"][$year] = 0;
			$diferences["AgentWageYears"][$year] -= $amount;
		}
		//.....................................................
		foreach($result_new["FundYearDelays"] as $year => $amount)
		{
			if(!isset($diferences["FundYearDelays"][$year]))
				$diferences["FundYearDelays"][$year] = 0;
			$diferences["FundYearDelays"][$year] += $amount;
		}
		foreach($result_old["FundYearDelays"] as $year => $amount)
		{
			if(!isset($diferences["FundYearDelays"][$year]))
				$diferences["FundYearDelays"][$year] = 0;
			$diferences["FundYearDelays"][$year] -= $amount;
		}
		//.....................................................
		foreach($result_new["AgentYearDelays"] as $year => $amount)
		{
			if(!isset($diferences["AgentYearDelays"][$year]))
				$diferences["AgentYearDelays"][$year] = 0;
			$diferences["AgentYearDelays"][$year] += $amount;
		}
		foreach($result_old["AgentYearDelays"] as $year => $amount)
		{
			if(!isset($diferences["AgentYearDelays"][$year]))
				$diferences["AgentYearDelays"][$year] = 0;
			$diferences["AgentYearDelays"][$year] -= $amount;
		}
		//.....................................................
		$diferences["TotalFundWage"] += $result_new["TotalFundWage"]*1 - $result_old["TotalFundWage"]*1;
		$diferences["TotalAgentWage"] += $result_new["TotalAgentWage"]*1 - $result_old["TotalAgentWage"]*1;
		$diferences["TotalCustomerWage"] += $result_new["TotalCustomerWage"]*1 - $result_old["TotalCustomerWage"]*1;
		$diferences["TotalFundDelay"] += $result_new["TotalFundDelay"]*1 - $result_old["TotalFundDelay"]*1;
		$diferences["TotalAgentDelay"] += $result_new["TotalAgentDelay"]*1 - $result_old["TotalAgentDelay"]*1;
	}
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
	
	$NewExtraAmount = GetExtraLoanAmount($NewPartObj, 
			$diferences["TotalFundWage"], 
			$diferences["TotalCustomerWage"], 
			$diferences["TotalAgentWage"], 
			$diferences["TotalFundDelay"], 
			$diferences["TotalAgentDelay"]);
	//.............. todiee ............................
	if($NewPartObj->PartAmount*1 != $PreviousPartObj->PartAmount*1)
	{
		$amount = $PreviousPartObj->PartAmount*1 - $NewPartObj->PartAmount*1;
		unset($itemObj->ItemID);
		$itemObj->DocID = $obj->DocID;
		$itemObj->CostID = $CostCode_todiee;
		$itemObj->TafsiliType = TAFTYPE_PERSONS;
		$itemObj->TafsiliID = $ReqPersonTafsili;
		unset($itemObj->TafsiliType2);
		unset($itemObj->TafsiliID2);
		$itemObj->DebtorAmount = $amount > 0 ? $amount : 0;
		$itemObj->CreditorAmount = $amount < 0 ? abs($amount) : 0;	
		$itemObj->Add($pdo);
		
		unset($itemObj->ItemID);
		$itemObj->DocID = $obj->DocID;
		$itemObj->CostID = $CostCode_Loan;
		$itemObj->DebtorAmount = $amount < 0 ? abs($amount) : 0;		
		$itemObj->CreditorAmount = $amount > 0 ? $amount : 0;
		$itemObj->Add($pdo);
	}
	//...............Loan ..............................
	if($NewExtraAmount != 0)
	{
		unset($itemObj->ItemID);
		$itemObj->DocID = $obj->DocID;
		$itemObj->CostID = $CostCode_Loan;
		$itemObj->DebtorAmount = $NewExtraAmount > 0 ? $NewExtraAmount : 0;
		$itemObj->CreditorAmount = $NewExtraAmount < 0 ? abs($NewExtraAmount) : 0;		
		$itemObj->Add($pdo);
	}
	//------------------------ agent delay -------------------------------
	$curYear = substr(DateModules::shNow(), 0, 4)*1;
	if($NewPartObj->AgentDelayReturn == "CUSTOMER")
	{
		unset($itemObj->ItemID);
		unset($itemObj->TafsiliType2);
		unset($itemObj->TafsiliID2);
		$itemObj->CostID = $CostCode_deposite;
		$itemObj->TafsiliType = TAFTYPE_PERSONS;
		$itemObj->TafsiliID = $ReqPersonTafsili;
		$itemObj->DebtorAmount = $diferences["TotalAgentDelay"]*1<0 ? abs($diferences["TotalAgentDelay"]*1) : 0;
		$itemObj->CreditorAmount = $diferences["TotalAgentDelay"]*1>0 ? $diferences["TotalAgentDelay"]*1 : 0;		
		$itemObj->details = "کارمزد دوره تنفس وام شماره " . $ReqObj->RequestID;
		if(!$itemObj->Add($pdo))
		{
			ExceptionHandler::PushException("خطا در ایجاد ردیف کارمزد تنفس");
			return false;
		}
		
		if($diferences["TotalAgentDelay"]*1 > 0)
		{
			$ExtraAmount += $diferences["TotalAgentDelay"]*1;
		}
		else
		{
			unset($itemObj->ItemID);
			unset($itemObj->TafsiliType2);
			unset($itemObj->TafsiliID2);
			$itemObj->CostID = COSTID_saving;
			$itemObj->TafsiliType = TAFTYPE_PERSONS;
			$itemObj->TafsiliID = $LoanPersonTafsili;
			$itemObj->CreditorAmount = 0;
			$itemObj->DebtorAmount = abs($diferences["TotalAgentDelay"]*1);
			$itemObj->details = "اختلاف کارمزد دوره تنفس وام شماره " . $ReqObj->RequestID;
			if(!$itemObj->Add($pdo))
			{
				ExceptionHandler::PushException("خطا در ایجاد ردیف کارمزد تنفس");
				return false;
			}
		}
			
		
	}
	else
	{
		$prevYear = 0;
		foreach($diferences["AgentYearDelays"] as $year => $value)
		{
			if($value == 0)
				continue;
			
			if($year*1 < $curYear)
			{
				$prevYear += $value*1;
				continue;
			}
			$value += $prevYear;

			unset($itemObj->ItemID);
			unset($itemObj->TafsiliType2);
			unset($itemObj->TafsiliID2);
			$itemObj->CostID = $year == $curYear ? $CostCode_agent_wage : $CostCode_agent_FutureWage;
			$itemObj->TafsiliType = TAFTYPE_YEARS;
			$itemObj->TafsiliID = FindTafsiliID($year, TAFTYPE_YEARS);
			$itemObj->DebtorAmount = $value<0 ? abs($value) : 0;
			$itemObj->CreditorAmount = $value>0 ? $value : 0;		
			$itemObj->details = "کارمزد دوره تنفس وام شماره " . $ReqObj->RequestID;
			if(!$itemObj->Add($pdo))
			{
				ExceptionHandler::PushException("خطا در ایجاد ردیف کارمزد تنفس");
				return false;
			}
			$prevYear = 0;
		}
		
		unset($itemObj->ItemID);
		unset($itemObj->TafsiliType2);
		unset($itemObj->TafsiliID2);
		$itemObj->CostID = $CostCode_Loan;
		$itemObj->TafsiliType = TAFTYPE_PERSONS;
		$itemObj->TafsiliID = $LoanPersonTafsili;
		$itemObj->TafsiliType2 = TAFTYPE_PERSONS;
		$itemObj->TafsiliID2 = $ReqPersonTafsili;
		$itemObj->CreditorAmount = $diferences["TotalAgentDelay"]*1<0 ? abs($diferences["TotalAgentDelay"]*1) : 0;
		$itemObj->DebtorAmount = $diferences["TotalAgentDelay"]*1>0 ? $diferences["TotalAgentDelay"]*1 : 0;			
		$itemObj->details = "اختلاف کارمزد دوره تنفس وام شماره " . $ReqObj->RequestID;
		if(!$itemObj->Add($pdo))
		{
			ExceptionHandler::PushException("خطا در ایجاد ردیف کارمزد تنفس");
			return false;
		}
		
	}
	//------------------------ fund delay -------------------------------
	$prevYear = 0;
	foreach($diferences["FundYearDelays"] as $year => $value)
	{
		if($value == 0)
			continue;
		if($year*1 < $curYear)
		{
			$prevYear += $value*1;
			continue;
		}
		$value += $prevYear;
		
		unset($itemObj->ItemID);
		unset($itemObj->TafsiliType2);
		unset($itemObj->TafsiliID2);
		$itemObj->CostID = $year == $curYear ? $CostCode_wage : $CostCode_FutureWage;
		$itemObj->TafsiliType = TAFTYPE_YEARS;
		$itemObj->TafsiliID = FindTafsiliID($year, TAFTYPE_YEARS);
		$itemObj->DebtorAmount = $value<0 ? abs($value) : 0;
		$itemObj->CreditorAmount = $value>0 ? $value : 0;		
		$itemObj->details = "کارمزد دوره تنفس وام شماره " . $ReqObj->RequestID;
		if(!$itemObj->Add($pdo))
		{
			ExceptionHandler::PushException("خطا در ایجاد ردیف کارمزد تنفس");
			return false;
		}
		if($NewPartObj->DelayReturn == "CUSTOMER")
			$ExtraAmount += $value;
		
		$prevYear = 0;
	}
	//------------------------ fund wage ---------------------	
	$prevYear = 0;
	foreach($diferences["FundWageYears"] as $Year => $amount)
	{
		if($amount == 0)
			continue;
		if($Year*1 < $curYear)
		{
			$prevYear += $amount*1;
			continue;
		}
		$amount += $prevYear;
		
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
		
		if($NewPartObj->WageReturn == "CUSTOMER")
			$ExtraAmount += $amount;
		
		$prevYear = 0;
	}
	//------------------------ agent wage ---------------------	
	if($NewPartObj->AgentReturn == "CUSTOMER")
	{
		if($diferences["TotalAgentWage"]*1 != 0)
		{
			unset($itemObj->ItemID);
			unset($itemObj->TafsiliType2);
			unset($itemObj->TafsiliID2);
			$itemObj->CostID = $CostCode_deposite;
			$itemObj->TafsiliType = TAFTYPE_YEARS;
			$itemObj->TafsiliID = FindTafsiliID($year, TAFTYPE_YEARS);
			$itemObj->DebtorAmount = $diferences["TotalAgentWage"]*1<0 ? abs($diferences["TotalAgentWage"]*1) : 0;
			$itemObj->CreditorAmount = $diferences["TotalAgentWage"]*1>0 ? $diferences["TotalAgentWage"]*1 : 0;		
			$itemObj->details = "کارمزد وام شماره " . $ReqObj->RequestID;
			if(!$itemObj->Add($pdo))
			{
				ExceptionHandler::PushException("خطا در ایجاد ردیف کارمزد تنفس");
				return false;
			}
			$ExtraAmount += $diferences["TotalAgentWage"]*1;
		}
	}
	else
	{
		$prevYear = 0;
		foreach($diferences["AgentWageYears"] as $Year => $amount)
		{
			if($amount == 0)
				continue;
			if($Year*1 < $curYear)
			{
				$prevYear += $amount*1;
				continue;
			}
			$amount += $prevYear;

			unset($itemObj->ItemID);
			$itemObj->CostID = $Year == $curYear ? $CostCode_agent_wage : $CostCode_agent_FutureWage;
			$itemObj->TafsiliType = TAFTYPE_YEARS;
			$itemObj->TafsiliID = $YearTafsili;
			$itemObj->details = "کارمزد وام شماره " . $ReqObj->RequestID;
			unset($itemObj->TafsiliType2);
			unset($itemObj->TafsiliID2);
			$itemObj->DebtorAmount = $amount<0 ? abs($amount) : 0;
			$itemObj->CreditorAmount = $amount>0 ? $amount : 0;
			if(!$itemObj->Add($pdo))
			{
				ExceptionHandler::PushException("خطا در ایجاد ردیف کارمزد ");
				return false;
			}
		}
	}
	// ---------------------------- ExtraAmount --------------------------------
	
	if($ExtraAmount > 0)
	{
		$itemObj = new ACC_DocItems();
		$itemObj->DocID = $obj->DocID;
		$itemObj->CostID = $CostCode_Loan;
		$itemObj->DebtorAmount = $ExtraAmount;
		$itemObj->CreditorAmount = 0;	
		$itemObj->locked = "NO";
		$itemObj->SourceID = $ReqObj->RequestID;
		$itemObj->SourceID2 = $NewPartObj->PartID;
		
		if(!$itemObj->Add($pdo))
		{
			ExceptionHandler::PushException("خطا در ایجاد ردیف مازاد");
			return false;
		}
		
		//-------------------- add loan cost for difference --------------------
		/*$dt = PdoDataAccess::runquery("select * from LON_costs where RequestID=? AND PartID=?", 
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
			$obj->CostDate = PDONOW;
			$obj->RequestID = $NewPartObj->RequestID;
			$obj->CostDesc = "اختلافت حاصل از تغییر شرایط پرداخت";
			$obj->CostAmount = $ExtraAmount;
			$obj->CostID = $CostCode_Loan;
			$obj->IsPartDiff = "YES";
			$obj->PartID = $NewPartObj->PartID;
			if(!$obj->Add($pdo))
			{
				ExceptionHandler::PushException("خطا در ایجاد هزینه");
				return false;
			}
		}*/
	}
	//--------------- differences of backPays -----------------
	$prevResult = ComputeWagesAndDelays($PreviousPartObj, $PreviousPartObj->PartAmount, $PreviousPartObj->PartDate, $PreviousPartObj->PartDate);
	$newResult = ComputeWagesAndDelays($NewPartObj, $NewPartObj->PartAmount, $NewPartObj->PartDate, $NewPartObj->PartDate);
	
	$PrevTotalBackPay = $PreviousPartObj->PartAmount*1 + 
		GetExtraLoanAmount($PreviousPartObj,$prevResult["TotalFundWage"],$prevResult["TotalCustomerWage"],
				$prevResult["TotalAgentWage"],$prevResult["TotalFundDelay"], $prevResult["TotalAgentDelay"]);
	$newTotalBackPay = $NewPartObj->PartAmount*1 + 
		GetExtraLoanAmount($NewPartObj,$newResult["TotalFundWage"],$newResult["TotalCustomerWage"],
				$newResult["TotalAgentWage"],$newResult["TotalFundDelay"], $newResult["TotalAgentDelay"]);
	
	$dt = LON_BackPays::GetRealPaid($RequestID);
	$prevRemain = $PrevTotalBackPay;
	$newRemain = $newTotalBackPay;
	$diffFundDelayShare = $diffAgentDelayShare = $diffFundWageShare = 0;
	foreach($dt as $row)
	{
		$prevFundDelayShare = $prevAgentDelayShare = $prevFundWageShare = 0;
		
		if($PreviousPartObj->DelayReturn == "INSTALLMENT")
		{
			$prevPayAmount = min($prevRemain, $row["PayAmount"]*1);
			$prevFundDelayShare = round($prevResult["TotalFundDelay"]*$prevPayAmount/$PrevTotalBackPay);
		}
		if($PreviousPartObj->AgentDelayReturn == "INSTALLMENT")
		{
			$prevPayAmount = min($prevRemain, $row["PayAmount"]*1);
			$prevAgentDelayShare = round($prevResult["TotalAgentDelay"]*$prevPayAmount/$PrevTotalBackPay);
		}
		if($PreviousPartObj->WageReturn == "INSTALLMENT")
		{
			$prevPayAmount = min($prevRemain, $row["PayAmount"]*1);
			$prevFundWageShare = round($prevResult["TotalFundWage"]*$prevPayAmount/$PrevTotalBackPay);
		}
		$prevRemain -= $prevPayAmount;
		//..............................................
		$newFundDelayShare = $newAgentDelayShare = $newFundWageShare = 0;
		
		if($NewPartObj->DelayReturn == "INSTALLMENT")
		{
			$newPayAmount = min($newRemain, $row["PayAmount"]*1);
			$newFundDelayShare = round($newResult["TotalFundDelay"]*$newPayAmount/$newTotalBackPay);
		}
		if($NewPartObj->AgentDelayReturn == "INSTALLMENT")
		{
			$newPayAmount = min($newRemain, $row["PayAmount"]*1);
			$newAgentDelayShare = round($newResult["TotalAgentDelay"]*$newPayAmount/$newTotalBackPay);
		}
		if($NewPartObj->WageReturn == "INSTALLMENT")
		{
			$newPayAmount = min($newRemain, $row["PayAmount"]*1);
			$newFundWageShare = round($newResult["TotalFundWage"]*$newPayAmount/$newTotalBackPay);
		}
		$newRemain -= $newPayAmount;
		
		$diffFundDelayShare += $newFundDelayShare - $prevFundDelayShare;
		$diffAgentDelayShare += $newAgentDelayShare - $prevAgentDelayShare;
		$diffFundWageShare += $newFundWageShare - $prevFundWageShare;
	}
	if($diffFundWageShare != 0)
	{
		$itemObj = new ACC_DocItems();
		$itemObj->DocID = $obj->DocID;
		$itemObj->CostID = $CostCode_wage;
		$itemObj->DebtorAmount = $diffFundWageShare < 0 ? abs($diffFundWageShare) : 0;
		$itemObj->CreditorAmount = $diffFundWageShare > 0 ? $diffFundWageShare : 0;	
		$itemObj->locked = "YES";
		$itemObj->details = "اختلاف پرداخت های مشتری وام شماره " . $ReqObj->RequestID;
		$itemObj->SourceID = $ReqObj->RequestID;
		$itemObj->SourceID2 = $NewPartObj->PartID;		
		if(!$itemObj->Add($pdo))
		{
			ExceptionHandler::PushException("خطا در ایجاد ردیف مازاد");
			return false;
		}
		$itemObj = new ACC_DocItems();
		$itemObj->DocID = $obj->DocID;
		$itemObj->CostID = $CostCode_deposite;
		$itemObj->DebtorAmount = $diffFundWageShare > 0 ? $diffFundWageShare : 0;	
		$itemObj->CreditorAmount = $diffFundWageShare < 0 ? abs($diffFundWageShare) : 0;
		$itemObj->locked = "YES";
		$itemObj->details = "اختلاف پرداخت های مشتری وام شماره " . $ReqObj->RequestID;
		$itemObj->SourceID = $ReqObj->RequestID;
		$itemObj->SourceID2 = $NewPartObj->PartID;		
		$itemObj->TafsiliType = TAFTYPE_PERSONS;
		$itemObj->TafsiliID = $ReqPersonTafsili;
		if(!$itemObj->Add($pdo))
		{
			ExceptionHandler::PushException("خطا در ایجاد ردیف مازاد");
			return false;
		}
	}
	//---------------------------------------------------------
	if(ExceptionHandler::GetExceptionCount() > 0)
		return false;
	
	return true;
}

function RegisterDifferncePartsDoc_Supporter($ReqObj, $NewPartObj, $pdo, $DocID=""){
	
	$dt = PdoDataAccess::runquery("select * from LON_ReqParts 
		where RequestID=? AND IsHistory='YES' order by PartID desc limit 1", array($ReqObj->RequestID));
	$PreviousPartObj = new LON_ReqParts($dt[0]["PartID"]);
	
	//------------- get CostCodes --------------------
	$LoanObj = new LON_loans($ReqObj->LoanID);
	$CostCode_Loan = FindCostID("110" . "-" . $LoanObj->_BlockCode);
	$CostCode_FundComit_wage = FindCostID("721-".$LoanObj->_BlockCode."-52-03");
	$CostCode_FundComit_mande = FindCostID("721-".$LoanObj->_BlockCode."-52-02");
	$CostCode_CustomerComit = FindCostID("721-".$LoanObj->_BlockCode."-51");
	//------------------------------------------------
	$CycleID = substr(DateModules::shNow(), 0 , 4);	
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
	PdoDataAccess::runquery("delete from LON_BackPays 
			where RequestID=? AND PayType=? AND PayBillNo=?", 
			array($ReqObj->RequestID, BACKPAY_PAYTYPE_CORRECT, $NewPartObj->PartID), $pdo);
	//--------------------------------------------------------
	$itemObj = new ACC_DocItems();
	$itemObj->DocID = $obj->DocID;
	$itemObj->locked = "YES";
	$itemObj->SourceType = DOCTYPE_LOAN_DIFFERENCE;
	$itemObj->SourceID = $ReqObj->RequestID;
	$itemObj->SourceID2 = $NewPartObj->PartID;
	$itemObj->TafsiliType = TAFTYPE_PERSONS;
	$itemObj->TafsiliID = $ReqPersonTafsili;
	//--------------------------------------------------------
	// compute the differnce of wage of CUSTOMER
	$PreviousAgentWage = 0;
	if($PreviousPartObj->AgentReturn == "CUSTOMER")
	{
		$totalWage = $PreviousPartObj->PartAmount*$PreviousPartObj->CustomerWage/100;
		$AgentFactor = ($PreviousPartObj->CustomerWage*1-$PreviousPartObj->FundWage*1)/$PreviousPartObj->CustomerWage*1;
		$PreviousAgentWage = $totalWage*$AgentFactor;		
	}
	$NewAgentWage = 0;
	if($NewPartObj->AgentReturn == "CUSTOMER")
	{
		$totalWage = $NewPartObj->PartAmount*$NewPartObj->CustomerWage/100;
		$AgentFactor = ($NewPartObj->CustomerWage*1-$NewPartObj->FundWage*1)/$NewPartObj->CustomerWage*1;
		$NewAgentWage = $totalWage*$AgentFactor;		
	}
	if($PreviousAgentWage <> $NewAgentWage)
	{
		$diff = $NewAgentWage - $PreviousAgentWage ;
		unset($itemObj->ItemID);
		$itemObj->CostID = COSTID_Bank;
		$itemObj->locked = "NO";
		$itemObj->DebtorAmount = $diff > 0 ? $diff : 0;
		$itemObj->CreditorAmount = $diff < 0 ? -1*$diff : 0;
		$itemObj->Add($pdo);
		
		unset($itemObj->ItemID);
		$itemObj->locked = "YES";
		$itemObj->CostID = $CostCode_FundComit_mande;
		$itemObj->DebtorAmount = $diff < 0 ? -1*$diff : 0;
		$itemObj->CreditorAmount = $diff > 0 ? $diff : 0;
		$itemObj->Add($pdo);		
	}
	//--------------------------------------------------------
	// compute the extra pay
	$dt = array();
	$compute = LON_requests::ComputePayments($NewPartObj->RequestID, $dt);
	$extraPay = $compute[ count($compute)-1 ]["TotalRemainder"]*1;
	if($extraPay < 0)
	{
		$extraPay= -1*$extraPay;
		
		unset($itemObj->ItemID);
		$itemObj->DocID = $obj->DocID;
		$itemObj->CostID = $CostCode_Loan;
		$itemObj->DebtorAmount = $extraPay;
		$itemObj->CreditorAmount = 0;
		$itemObj->Add($pdo);
		
		unset($itemObj->ItemID);
		$itemObj->CostID = $CostCode_CustomerComit;
		$itemObj->CreditorAmount = $extraPay;
		$itemObj->DebtorAmount = 0;
		$itemObj->Add($pdo);
		
		$backPayObj = new LON_BackPays();
		$backPayObj->RequestID = $ReqObj->RequestID;
		$backPayObj->PayAmount = -1*$extraPay;
		$backPayObj->PayDate = $NewPartObj->PartDate;
		$backPayObj->PayBillNo = $NewPartObj->PartID;
		$backPayObj->PayType = BACKPAY_PAYTYPE_CORRECT;
		$backPayObj->details = "اختلاف کارمزد حاصل از تغییر شرایط پرداخت";
		$backPayObj->Add($pdo);
		
	}
	//--------------------------------------------------------
	// compute the differnce of wage of INSTALLMENT
	$PreviousInstallmentWage = 0;
	if($PreviousPartObj->AgentReturn == "INSTALLMENT")
		$PreviousInstallmentWage = ComputeWageOfSHekoofa($PreviousPartObj);		

	$NewInstallmentWage = 0;
	if($NewPartObj->AgentReturn == "INSTALLMENT")
		$NewInstallmentWage = ComputeWageOfSHekoofa($NewPartObj);
	
	if($PreviousInstallmentWage <> $NewInstallmentWage)
	{
		$diff = $NewInstallmentWage - $PreviousInstallmentWage ;
		
		unset($itemObj->ItemID);
		$itemObj->DocID = $obj->DocID;
		$itemObj->CostID = $CostCode_Loan;
		$itemObj->DebtorAmount = $diff>0 ? $diff : 0;
		$itemObj->CreditorAmount = $diff<0 ? -1*$diff : 0;
		$itemObj->Add($pdo);
		
		unset($itemObj->ItemID);
		$itemObj->CostID = $CostCode_CustomerComit;
		$itemObj->CreditorAmount = $diff>0 ? $diff : 0;
		$itemObj->DebtorAmount = $diff<0 ? -1*$diff : 0;
		$itemObj->Add($pdo);
		
		if($diff < 0)
		{
			$backPayObj = new LON_BackPays();
			$backPayObj->RequestID = $ReqObj->RequestID;
			$backPayObj->PayAmount = $diff;
			$backPayObj->PayDate = $NewPartObj->PartDate;
			$backPayObj->PayBillNo = $NewPartObj->PartID;
			$backPayObj->PayType = BACKPAY_PAYTYPE_CORRECT;
			$backPayObj->details = "اختلاف کارمزد حاصل از تغییر شرایط پرداخت";
			$backPayObj->Add($pdo);
		}
	}
	//---------------------------------------------------------
	if(ExceptionHandler::GetExceptionCount() > 0)
		return false;
	
	return true;
}
//---------------------------------------------------------------
function RegisterChangeInstallmentWage($DocID, $ReqObj,$PartObj, $InstallmentObj, $newDate, $wage, $pdo){
		
	//------------- get CostCodes --------------------
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
	}	
	//------------------------------------------------
	$LoanObj = new LON_loans($ReqObj->LoanID);
	$CostCode_Loan = FindCostID("110" . "-" . $LoanObj->_BlockCode);
	$CostCode_wage = FindCostID("750" . "-" . $LoanObj->_BlockCode);
	$CostCode_FutureWage = FindCostID("760" . "-" . $LoanObj->_BlockCode);
	$CostCode_agent_wage = FindCostID("750" . "-52");
	$CostCode_agent_FutureWage = FindCostID("760" . "-52");
	//------------------------------------------------
	$CycleID = substr(DateModules::shNow(), 0 , 4);	
	//---------------- add doc header --------------------
	if($DocID == "")
	{
		$obj = new ACC_docs();
		$obj->RegDate = PDONOW;
		$obj->regPersonID = $_SESSION['USER']["PersonID"];
		$obj->DocDate = PDONOW;
		$obj->CycleID = $CycleID;
		$obj->BranchID = $ReqObj->BranchID;
		$obj->DocType = DOCTYPE_INSTALLMENT_CHANGE;
		$obj->description = "اختلاف حاصل از تغییر قسط وام شماره " . $ReqObj->RequestID;
		if(!$obj->Add($pdo))
		{
			ExceptionHandler::PushException("خطا در ایجاد سند");
			return false;
		}
	}
	else
	{
		$obj = new ACC_docs($DocID);
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
	$itemObj->SourceType = DOCTYPE_INSTALLMENT_CHANGE;
	$itemObj->SourceID = $ReqObj->RequestID;
	$itemObj->SourceID2 = $InstallmentObj->InstallmentID;
	
	//...............Loan ..............................
	$itemObj->CostID = $CostCode_Loan;
	$itemObj->DebtorAmount = $wage > 0 ? $wage : 0;
	$itemObj->CreditorAmount = $wage < 0 ? abs($wage) : 0;
	$itemObj->Add($pdo);
	//..........  wage .................................
	unset($itemObj->TafsiliType2);
	unset($itemObj->TafsiliID2);
	
	$MaxWage =		max($PartObj->CustomerWage*1 , $PartObj->FundWage);
	$FundFactor =	$MaxWage == 0 ? 0 : $PartObj->FundWage/$MaxWage;
	$AgentFactor =	$MaxWage == 0 ? 0 : ($PartObj->CustomerWage-$PartObj->FundWage)/$MaxWage;
	
	$curYear = $_SESSION["accounting"]["CycleYear"]*1;
	if($wage < 0)
		$years = SplitYears($newDate, $InstallmentObj->InstallmentDate, $wage);
	else
		$years = SplitYears($InstallmentObj->InstallmentDate, $newDate, $wage);
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
		$itemObj->CostID = $Year == $curYear ? $CostCode_wage : $CostCode_FutureWage;
		$itemObj->TafsiliType = TAFTYPE_YEARS;
		$itemObj->TafsiliID = $YearTafsili;
		$itemObj->CreditorAmount = round($FundFactor*$amount) > 0 ? round($FundFactor*$amount) : 0;
		$itemObj->DebtorAmount = round($FundFactor*$amount) < 0 ? abs(round($FundFactor*$amount)) : 0;
		$itemObj->Add($pdo);
		
		if($AgentFactor > 0)
		{
			unset($itemObj->ItemID);
			$itemObj->CostID = $Year == $curYear ? $CostCode_agent_wage : $CostCode_agent_FutureWage;
			$itemObj->TafsiliType = TAFTYPE_YEARS;
			$itemObj->TafsiliID = $YearTafsili;
			$itemObj->CreditorAmount = round($AgentFactor*$amount) > 0 ? round($AgentFactor*$amount) : 0;
			$itemObj->DebtorAmount = round($AgentFactor*$amount) < 0 ? abs(round($AgentFactor*$amount)) : 0;
			$itemObj->Add($pdo);
		}
	}
	//---------------------------------------------------------
	if(ExceptionHandler::GetExceptionCount() > 0)
		return false;
	
	return $obj->DocID;
}

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
	//----------------------compute -----------------------
	
	$firstPayDate = $PartObj->PartDate;
	$PayObj->PayAmount = $PayObj->PayAmount*1;
	$result = ComputeWagesAndDelays($PartObj, $PartObj->PartAmount, $firstPayDate, $PartObj->PartDate);
	$TotalFundWage = $result["TotalFundWage"];
	$TotalCustomerWage = $result["TotalFundWage"];
	$TotalAgentWage = $result["TotalAgentWage"];
	$TotalFundDelay = $result["TotalFundDelay"];
	$TotalAgentDelay = $result["TotalAgentDelay"];
	$totalBackPay = $PartObj->PartAmount*1 + 
		GetExtraLoanAmount($PartObj,$TotalFundWage,$TotalCustomerWage,$TotalAgentWage,$TotalFundDelay, $TotalAgentDelay);
	
	$dt = LON_BackPays::GetRealPaid($PayObj->RequestID);
	$remain = $totalBackPay;
	$FundDelayShare = $AgentDelayShare = $FundWageShare = 0;
	foreach($dt as $row)
	{
		if($PartObj->DelayReturn == "INSTALLMENT")
		{
			$payAmount = min($remain, $row["PayAmount"]*1);
			$FundDelayShare = round($TotalFundDelay*$payAmount/$totalBackPay);
		}
		if($PartObj->AgentDelayReturn == "INSTALLMENT")
		{
			$payAmount = min($remain, $row["PayAmount"]*1);
			$AgentDelayShare = round($TotalAgentDelay*$payAmount/$totalBackPay);
		}
		if($PartObj->WageReturn == "INSTALLMENT")
		{
			$payAmount = min($remain, $row["PayAmount"]*1);
			$FundWageShare = round($TotalFundWage*$payAmount/$totalBackPay);
		}
		$remain -= $payAmount;
	}
	
	//----------------- get total remain ---------------------
	PdoDataAccess::runquery("delete from LON_BackPays 
		where RequestID=? AND PayType=? AND PayBillNo=?", 
		array($ReqObj->RequestID, BACKPAY_PAYTYPE_CORRECT, $PayObj->BackPayID));
	
	require_once getenv("DOCUMENT_ROOT") . '/loan/request/request.class.php';
	$dt = array();
	$returnArr = LON_requests::ComputePayments2($PayObj->RequestID, $dt, $pdo);
	$ExtraPay = 0;
	if($returnArr[ count($returnArr)-1 ]["TotalRemainder"]*1 + $returnArr[ count($returnArr)-1 ]["ForfeitAmount"]*1 < 0)
	{
		$ExtraPay = abs($returnArr[ count($returnArr)-1 ]["TotalRemainder"]*1 + 
						$returnArr[ count($returnArr)-1 ]["ForfeitAmount"]*1);
		$ExtraPay = min($ExtraPay,$PayObj->PayAmount*1);
		
		$backPayObj = new LON_BackPays();
		$backPayObj->RequestID = $PayObj->RequestID;
		$backPayObj->PayAmount = -1*$ExtraPay;
		$backPayObj->PayDate = $PayObj->PayDate;
		$backPayObj->PayType = BACKPAY_PAYTYPE_CORRECT;
		$backPayObj->PayBillNo = $PayObj->BackPayID;
		$backPayObj->details = "بابت اضافه پرداختی مشتری و انتقال به حساب قرض الحسنه";
		$backPayObj->Add($pdo);	
		
		$PayObj->PayAmount = $PayObj->PayAmount*1 - $ExtraPay;
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
	unset($itemObj->TafsiliType);
	unset($itemObj->TafsiliID);
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
	if($LoanMode == "Agent")
	{
		unset($itemObj->ItemID);
		$itemObj->CostID = $CostCode_commitment;
		$itemObj->DebtorAmount = $PayObj->PayAmount;
		$itemObj->CreditorAmount = 0;
		$itemObj->TafsiliType = TAFTYPE_PERSONS;
		$itemObj->TafsiliID = $ReqPersonTafsili;
		if(!$itemObj->Add($pdo))
		{
			ExceptionHandler::PushException("خطا در ایجاد ردیف تعهد");
			return false;
		}
	}
	//------------------- delay amount ---------------------
	if($PartObj->DelayReturn == "INSTALLMENT" && $LoanMode == "Agent" && $FundDelayShare > 0)
	{
		unset($itemObj->ItemID);
		unset($itemObj->TafsiliType);
		unset($itemObj->TafsiliID);
		unset($itemObj->TafsiliType2);
		unset($itemObj->TafsiliID2);
		$itemObj->CostID = $CostCode_wage;
		$itemObj->details = "بابت تنفس وام " . $PayObj->RequestID;
		$itemObj->DebtorAmount = 0;
		$itemObj->SourceID3 = "1";
		$itemObj->CreditorAmount = $FundDelayShare;
		if(!$itemObj->Add($pdo))
		{
			ExceptionHandler::PushException("خطا در ایجاد سند");
			return false;
		}
		$PayObj->PayAmount = $PayObj->PayAmount - $FundDelayShare;
		unset($itemObj->SourceID3);
	}
	if($PayObj->PayAmount > 0 && $LoanMode == "Agent" && $PartObj->AgentDelayReturn == "INSTALLMENT" && $AgentDelayShare > 0)
	{
		unset($itemObj->ItemID);
		unset($itemObj->TafsiliType2);
		unset($itemObj->TafsiliID2);
		$itemObj->CostID = $CostCode_deposite;
		$itemObj->DebtorAmount = 0;
		$itemObj->details = "بابت تنفس وام " . $PayObj->RequestID;
		$itemObj->SourceID3 = "1";
		$itemObj->CreditorAmount = $AgentDelayShare;
		$itemObj->TafsiliType = TAFTYPE_PERSONS;
		$itemObj->TafsiliID = $ReqPersonTafsili;
		if(!$itemObj->Add($pdo))
		{
			ExceptionHandler::PushException("خطا در ایجاد سند");
			return false;
		}
		$PayObj->PayAmount = $PayObj->PayAmount - $AgentDelayShare;
		unset($itemObj->SourceID3);
	}
	//------------------ forfeit amount -----------------------
	$forfeitAmount = $returnArr[ count($returnArr)-2 ]["ForfeitAmount"]*1 - 
			$returnArr[ count($returnArr)-1 ]["ForfeitAmount"]*1;		
	
	if($forfeitAmount > 0 && $LoanMode == "Agent")
	{
		unset($itemObj->ItemID);
		unset($itemObj->TafsiliType);
		unset($itemObj->TafsiliID);
		unset($itemObj->TafsiliType2);
		unset($itemObj->TafsiliID2);
		$itemObj->details = "مبلغ تاخیر وام شماره " . $ReqObj->RequestID ;
		$itemObj->CostID = $CostCode_wage;
		$itemObj->DebtorAmount = 0;
		$itemObj->CreditorAmount = $forfeitAmount;
		if(!$itemObj->Add($pdo))
		{
			ExceptionHandler::PushException("خطا در ایجاد ردیف سپرده");
			return false;
		}
		$PayObj->PayAmount -= $forfeitAmount;
	}
	//------------- wage --------------
	if($LoanMode == "Agent" && $PayObj->PayAmount*1 > 0)
	{
		if($PartObj->WageReturn == "INSTALLMENT" && $FundWageShare > 0)
		{
			$PayObj->PayAmount -= $FundWageShare;
			
			unset($itemObj->ItemID);
			unset($itemObj->TafsiliType);
			unset($itemObj->TafsiliID);
			unset($itemObj->TafsiliType2);
			unset($itemObj->TafsiliID2);
			$itemObj->details = "کارمزد صندوق وام شماره " . $ReqObj->RequestID ;
			$itemObj->CostID = $CostCode_wage;
			$itemObj->DebtorAmount = 0;
			$itemObj->CreditorAmount = $FundWageShare;
			if(!$itemObj->Add($pdo))
			{
				ExceptionHandler::PushException("خطا در ایجاد ردیف سپرده");
				return false;
			}
		}
		//---- اضافه به سپرده -----
		unset($itemObj->ItemID);
		unset($itemObj->TafsiliType2);
		unset($itemObj->TafsiliID2);
		$itemObj->details = "پرداخت قسط وام شماره " . $ReqObj->RequestID ;
		$itemObj->CostID = $CostCode_deposite;
		$itemObj->DebtorAmount = 0;
		$itemObj->CreditorAmount = $PayObj->PayAmount*1;
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
	}
	
	//---------------------------------------------------------
	//print_r(ExceptionHandler::PopAllExceptions());
	if(ExceptionHandler::GetExceptionCount() > 0)
		return false;
		
	return true;
}

function RegisterSHRTFUNDCustomerPayDoc($DocObj, $PayObj, $CostID, $TafsiliID, $TafsiliID2, 
		$CenterAccount, $BranchID, $FirstCostID, $SecondCostID, $pdo, $grouping=false){
	
	/*@var $PayObj LON_BackPays */
	$ReqObj = new LON_requests($PayObj->RequestID);
	$PartObj = LON_ReqParts::GetValidPartObj($PayObj->RequestID);
	
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
	$CostCode_varizi = FindCostID("721-".$LoanObj->_BlockCode."-52-01");
	$CostCode_CustomerComit = FindCostID("721-".$LoanObj->_BlockCode."-51");
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
	//----------------- get total remain ---------------------
	PdoDataAccess::runquery("delete from LON_BackPays 
		where RequestID=? AND PayType=? AND PayBillNo=?", 
		array($ReqObj->RequestID, BACKPAY_PAYTYPE_CORRECT, $PayObj->BackPayID));
	
	require_once getenv("DOCUMENT_ROOT") . '/loan/request/request.class.php';
	$dt = array();
	$returnArr = LON_requests::ComputePayments($PayObj->RequestID, $dt, $pdo);
	$ExtraPay = 0;
	if(count($returnArr)>0 && $returnArr[ count($returnArr)-1 ]["TotalRemainder"]*1 < 0)
	{
		$ExtraPay = $returnArr[ count($returnArr)-1 ]["TotalRemainder"]*-1;
		$ExtraPay = min($ExtraPay,$PayObj->PayAmount*1);
		
		$backPayObj = new LON_BackPays();
		$backPayObj->RequestID = $PayObj->RequestID;
		$backPayObj->PayAmount = -1*$ExtraPay;
		$backPayObj->PayDate = $PayObj->PayDate;
		$backPayObj->PayType = BACKPAY_PAYTYPE_CORRECT;
		$backPayObj->PayBillNo = $PayObj->BackPayID;
		$backPayObj->details = "بابت اضافه پرداختی مشتری";
		$backPayObj->Add($pdo);		
		
		$PayObj->PayAmount =  $PayObj->PayAmount*1 - $ExtraPay;
		
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
	$itemObj->CostID = $CostCode_CustomerComit;
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
		$itemObj->DebtorAmount= $PayObj->PayAmount + $ExtraPay;
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
		$itemObj->CreditorAmount = $PayObj->PayAmount + $ExtraPay;
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
			ExceptionHandler::PushException("خطا در ایجاد سند");
			return false;
		}
	}
	//-------------- extra to Pasandaz ----------------
	if($ExtraPay > 0)
	{
		unset($itemObj->ItemID);
		$itemObj->DocID = $obj->DocID;
		$itemObj->CostID = $CostCode_CustomerComit;
		$itemObj->DebtorAmount = 0;
		$itemObj->CreditorAmount = $ExtraPay;
		$itemObj->TafsiliType = TAFTYPE_PERSONS;
		$itemObj->TafsiliID = $ReqPersonTafsili;
		if(!$itemObj->Add($pdo))
		{
			ExceptionHandler::PushException("خطا در ایجاد ردیف وام");
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
		$dt = PdoDataAccess::runquery("select max(SourceID)
			from ACC_docs join ACC_DocItems using(DocID)
			where DocType=" . DOCTYPE_DEPOSIT_PROFIT . " 
				AND CycleID=" . $_SESSION["accounting"]["CycleID"] . "
				AND BranchID=" . $_SESSION["accounting"]["BranchID"] . "	
				AND TafsiliID=?
			order by DocID desc", array($TafsiliID));

		$LatestComputeDate = count($dt)==0 || $dt[0][0] == "" ? $FirstYearDay : $dt[0][0];

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
			where DocDate <= ? 
				AND CostID in(" . COSTID_ShortDeposite . "," . COSTID_LongDeposite . ")
				AND CycleID=" . $_SESSION["accounting"]["CycleID"] . "
				AND BranchID=" . $_SESSION["accounting"]["BranchID"] . "
				AND TafsiliID=?
			group by TafsiliID,CostID", array($LatestComputeDate,$TafsiliID));

		foreach($dt as $row)
		{
			$DepositeAmount[ $row["CostID"] ][ $row["TafsiliID"] ]["amount"] = $row["amount"];
			$DepositeAmount[ $row["CostID"] ][ $row["TafsiliID"] ]["lastDate"] = $LatestComputeDate;
			
			$row["DocDate"] = $LatestComputeDate;
			$row["DocDesc"] = "مانده قبل";
			$TraceArr[ $row["TafsiliID"] ][] = array(
				"row" => $row,
				"profit" => 0,
				"days" => 0
			);
		}
		//------------ get the Deposite amount -------------
		$dt = PdoDataAccess::runquery("
			select CostID,TafsiliID,DocDate,group_concat(details SEPARATOR '<br>') DocDesc,sum(CreditorAmount-DebtorAmount) amount
			from ACC_DocItems 
				join ACC_docs using(DocID)
			where CostID in(" . COSTID_ShortDeposite . "," . COSTID_LongDeposite . ")
				AND DocDate > ? AND DocDate <= ?
				AND TafsiliID=?
				AND CycleID=" . $_SESSION["accounting"]["CycleID"] . "
				AND BranchID=" . $_SESSION["accounting"]["BranchID"] . "
			group by DocDate
			order by DocDate", 
				array($LatestComputeDate, $ToDate, $TafsiliID));
		
		$prevDays = 0;
		foreach($dt as $row)
		{
			if(!isset($DepositeAmount[ $row["CostID"] ][ $row["TafsiliID"] ]["lastDate"]))
			{
				$DepositeAmount[ $row["CostID"] ][ $row["TafsiliID"] ]["lastDate"] = $FirstYearDay;
				$DepositeAmount[ $row["CostID"] ][ $row["TafsiliID"] ]["amount"] = 0;
			}
			$lastDate = $DepositeAmount[ $row["CostID"] ][ $row["TafsiliID"] ]["lastDate"];
			$days = DateModules::GDateMinusGDate($row["DocDate"], $lastDate);
			
			if($row["amount"]*1 < 0)
			{
				$days--;
				$days += $prevDays;
				$prevDays = 1;
			}
			else
			{
				$days += $prevDays;
				$prevDays = 0;
			}

			$amount = $DepositeAmount[ $row["CostID"] ][ $row["TafsiliID"] ]["amount"] * $days * 
				$DepositePercents[ $row["CostID"] ]/(36500);

			if(!isset($DepositeAmount[ $row["CostID"] ][ $row["TafsiliID"] ]["profit"]))
				$DepositeAmount[ $row["CostID"] ][ $row["TafsiliID"] ]["profit"] = 0;
			$DepositeAmount[ $row["CostID"] ][ $row["TafsiliID"] ]["profit"] += $amount;

			$DepositeAmount[ $row["CostID"] ][ $row["TafsiliID"] ]["amount"] += $row["amount"];
			$DepositeAmount[ $row["CostID"] ][ $row["TafsiliID"] ]["lastDate"] = $row["DocDate"];	
			
			$arr = &$TraceArr[ $row["TafsiliID"] ];
			$arr[count($arr)-1]["days"] = $days;
			$arr[count($arr)-1]["profit"] = $amount;
			
			$TraceArr[ $row["TafsiliID"] ][] = array(
				"row" => $row,
				"days" => 0,
				"profit" => 0
			);
		}
		//--------------------- compute untill toDate ------------------------------
		foreach($DepositeAmount[ COSTID_ShortDeposite ] as $tafsili => &$row)
		{
			$days = DateModules::GDateMinusGDate($ToDate, $row["lastDate"]);
			$days += $prevDays;
			$amount = $row["amount"] * $days * $DepositePercents[ COSTID_ShortDeposite ]/(36500);

			if(!isset($row["profit"]))
				$row["profit"] = 0;
			$row["profit"] += $amount;

			$arr = &$TraceArr[ $tafsili ];
			$arr[count($arr)-1]["days"] = $days;
			$arr[count($arr)-1]["profit"] = $amount;
		}
		foreach($DepositeAmount[ COSTID_LongDeposite ] as $tafsili => &$row)
		{
			$days = DateModules::GDateMinusGDate($ToDate, $row["lastDate"]);
			$days += $prevDays;
			$amount = $row["amount"] * $days * $DepositePercents[ COSTID_LongDeposite ]/(36500);

			if(!isset($row["profit"]))
				$row["profit"] = 0;
			$row["profit"] += $amount;

			$arr = &$TraceArr[ $tafsili ];
			$arr[count($arr)-1]["days"] = $days;
			$arr[count($arr)-1]["profit"] = $amount;
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
			$itemObj->details = "سود سپرده تا تاریخ " . DateModules::miladi_to_shamsi($ToDate);
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
	$itemObj->CostID = COSTID_Wage;
	$itemObj->DebtorAmount= $sumAmount;
	$itemObj->CreditorAmount = 0;
	$itemObj->locked = "YES";
	$itemObj->SourceType = DOCTYPE_DEPOSIT_PROFIT;
	if(!$itemObj->Add($pdo))
	{
		print_r(ExceptionHandler::PopAllExceptions());
		echo Response::createObjectiveResponse(false, "خطا در ایجاد ردیف سند");
		die();
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

function RegisterWarrantyDoc($ReqObj, $WageCost, $TafsiliID, $TafsiliID2,$Block_CostID, $DocID, $pdo){
	
	/*@var $ReqObj WAR_requests */
	$IsExtend = $ReqObj->RefRequestID != $ReqObj->RequestID ? true : false;
	
	//------------- get CostCodes --------------------
	$CostCode_warrenty = FindCostID("300");
	$CostCode_warrenty_commitment = FindCostID("700");
	$CostCode_wage = FindCostID("750-07");
	$CostCode_FutureWage = FindCostID("760-07");
	$CostCode_seporde = FindCostID("690");
	$CostCode_pasandaz = FindCostID("209-10");
	
	$CostCode_guaranteeAmount_zemanati = FindCostID("904-02");
	$CostCode_guaranteeAmount2_zemanati = FindCostID("905-02");
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
	if(!$IsExtend && $WageCost == $CostCode_pasandaz && $dt[0][0]*1 < $ReqObj->amount*0.1)
	{
		ExceptionHandler::PushException("مانده حساب پس انداز مشتری کمتر از 10% مبلغ ضمانت نامه می باشد");
		return false;
	}
	$totalAmount = $IsExtend ? $TotalWage : ($ReqObj->amount*0.1 + $TotalWage);
	if($WageCost == $CostCode_pasandaz && $dt[0][0]*1 < $totalAmount)
	{
		ExceptionHandler::PushException("مانده حساب پس انداز مشتری کمتر از مبلغ کارمزد می باشد");
		return false;
	}
	if(!$IsExtend && $ReqObj->IsBlock == "YES")
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
		if($WageCost == $CostCode_pasandaz)
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
		$DocObj->DocType = $IsExtend ? DOCTYPE_WARRENTY_EXTEND : DOCTYPE_WARRENTY;
		$DocObj->description = ($IsExtend ? "تمدید " : "") . 
				"ضمانت نامه " . $ReqObj->_TypeDesc . " به شماره " . 
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
	$itemObj->SourceID = $ReqObj->RefRequestID;
	$itemObj->SourceID2 = $ReqObj->RequestID;
	$itemObj->locked = "YES";
	
	if(!$IsExtend)
	{
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
	if(!$IsExtend && $ReqObj->IsBlock == "YES")
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
		$itemObj->SourceID = $IsExtend ? $ReqObj->RefRequestID : $ReqObj->RequestID;
		$itemObj->SourceID2 = $ReqObj->RequestID;
		$itemObj->SourceID3 = $row["CostID"];
		$itemObj->details = $row["CostDesc"];
		$itemObj->CostID = $row["CostCodeID"];
		$itemObj->DebtorAmount = $row["CostType"] == "DEBTOR" ? $row["CostAmount"] : 0;
		$itemObj->CreditorAmount = $row["CostType"] == "CREDITOR" ? $row["CostAmount"] : 0;
		$itemObj->Add($pdo);
	}
	// ----------------------------- bank --------------------------------
	if(!$IsExtend)
	{
		unset($itemObj->ItemID);
		unset($itemObj->TafsiliType);
		unset($itemObj->TafsiliID);
		$itemObj->details = "بابت 10% سپرده ضمانت نامه شماره " . $ReqObj->RequestID;
		$itemObj->CostID = $CostCode_seporde;
		$itemObj->DebtorAmount = 0;
		$itemObj->CreditorAmount = $ReqObj->amount*0.1;
		$itemObj->Add($pdo);
	}
	
	unset($itemObj->ItemID);
	$CostObj = new ACC_CostCodes($WageCost);
	$itemObj->details = "بابت کارمزد ضمانت نامه شماره " . $ReqObj->RequestID;
	$itemObj->CostID = $WageCost;
	$itemObj->DebtorAmount = $TotalWage + (!$IsExtend ? $ReqObj->amount*0.1 : 0) - $totalCostAmount;
	$itemObj->CreditorAmount = 0;
	$itemObj->TafsiliType = $CostObj->TafsiliType;
	if($TafsiliID != "")
		$itemObj->TafsiliID = $TafsiliID;
	$itemObj->TafsiliType2 = $CostObj->TafsiliType2;
	if($TafsiliID2 != "")
		$itemObj->TafsiliID2 = $TafsiliID2;
	$itemObj->SourceID = $IsExtend ? $ReqObj->RefRequestID : $ReqObj->RequestID;
	$itemObj->SourceID2 = $ReqObj->RequestID;
	$itemObj->Add($pdo);
	
	//---------- ردیف های تضمین  ----------
	if(!$IsExtend)
	{
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
	}
	
	if(ExceptionHandler::GetExceptionCount() > 0)
		return false;
	
	return $DocObj->DocID;
}

function ReturnWarrantyDoc($ReqObj, $pdo, $EditMode = false){
	
	/*@var $PayObj WAR_requests */
	
	$dt = PdoDataAccess::runquery("select DocID from ACC_DocItems 
		where SourceType=" . DOCTYPE_WARRENTY . " AND SourceID2=?",
		array($ReqObj->RequestID), $pdo);
	if(count($dt) == 0)
		return true;
	
	PdoDataAccess::runquery("delete from ACC_CostBlocks 
			where SourceType=" . DOCTYPE_WARRENTY . " AND SourceID=?",
			array($ReqObj->RequestID), $pdo);
	
	if($EditMode)
	{
		PdoDataAccess::runquery("delete from ACC_DocItems 
			where SourceType=" . DOCTYPE_WARRENTY . " AND SourceID2=?",
			array($ReqObj->RequestID), $pdo);
		
		return true;
	}
	
	return ACC_docs::Remove($dt[0][0], $pdo);
}

function EndWarrantyDoc($ReqObj, $pdo){
	
	/*@var $ReqObj WAR_requests */
	
	//------------- get CostCodes --------------------
	$CostCode_warrenty = FindCostID("300");
	$CostCode_warrenty_commitment = FindCostID("700");
	
	$CostCode_guaranteeAmount_zemanati = FindCostID("904-02");
	$CostCode_guaranteeAmount2_zemanati = FindCostID("905-02");
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
	$itemObj->SourceID = $ReqObj->RefRequestID;
	$itemObj->SourceID2 = $ReqObj->RequestID;
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
		$itemObj->CreditorAmount = $row["ParamValue"];
		$itemObj->DebtorAmount  = 0;
		$itemObj->TafsiliType = TAFTYPE_PERSONS;
		$itemObj->TafsiliID = $PersonTafsili;
		$itemObj->SourceType = DOCTYPE_DOCUMENT;
		$itemObj->SourceID = $row["DocumentID"];
		$itemObj->details = $row["DocTypeDesc"];
		$itemObj->Add($pdo);

		$SumAmount += $row["ParamValue"]*1;
	}
	if($SumAmount > 0)
	{
		unset($itemObj->ItemID);
		unset($itemObj->TafsiliType);
		unset($itemObj->TafsiliID);
		unset($itemObj->details);
		$itemObj->CostID = $CostCode_guaranteeAmount2_zemanati;
		$itemObj->CreditorAmount = 0;
		$itemObj->DebtorAmount = $SumAmount;	
		$itemObj->Add($pdo);
	}

	if(ExceptionHandler::GetExceptionCount() > 0)
		return false;
	
	return $DocObj->DocID;
}

//---------------------------------------------------------------

?>
