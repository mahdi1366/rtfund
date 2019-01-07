<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 97.09
//-----------------------------

class EventComputeItems {
	
	static function PayLoan($ItemID, $SourceObjects){
		
		$ReqObj = $SourceObjects[0];
		$PartObj = $SourceObjects[1];
		$PayObj = $SourceObjects[2];
		/* @var $ReqObj LON_requests */
		/* @var $PartObj LON_ReqParts */
		/* @var $PayObj LON_payments */

		switch($ItemID){
			
			case 1 : // مبلغ اصل تسهیلات
				return $PartObj->PartAmount;
			
			case 3 : //مبلغ قابل پرداخت دراین مرحله	
				return $PayObj->PayAmount;
				
			case 4 :  //مبلغ قابل پرداخت در مراحل بعد
				return $PartObj->PartAmount*1 - $PayObj->PayAmount*1;
				
			case 5 : // مبلغ قابل پرداخت به مشتری
				return LON_requests::GetPurePayedAmount($ReqObj->RequestID);
				
			case 2 : //مبلغ کل کارمزد
			case 12 : //مبلغ کارمزد سهم سرمایه گذار 
			case 13 : //مبلغ کارمزد سهم صندوق 
				
				$result = LON_requests::GetWageAmounts($ReqObj->RequestID);
				if($ItemID == 2)
					return $result["CustomerWage"];
				if($ItemID == 12)
					return $result["AgentWage"];
				if($ItemID == 13)
					return $result["FundWage"];
				
			case 16 : // مبلغ کارمزد تحقق یافته سرمایه گذار 
			case 17 : // مبلغ کارمزد تحقق یافته صندوق 
			case 14 : //مبلغ کارمزد تحقق نیافته سرمایه گذار
			case 15 : // مبلغ کارمزد تحقق نیافته صندوق
				
				$getWage = $PartObj->PartAmount*1 - LON_requests::GetPurePayedAmount($ReqObj->RequestID);
				$FundFactor = $PartObj->FundWage/$PartObj->CustomerWage*1;
				$AgentFactor = ($PartObj->CustomerWage-$PartObj->FundWage)/$PartObj->CustomerWage*1;
				if($ItemID == 16)
					return round($getWage*$AgentFactor);
				if($ItemID == 17)
					return round($getWage*$FundFactor);
				
				$result = LON_requests::GetWageAmounts($ReqObj->RequestID);
				if($ItemID == 14)
					return round($result["AgentWage"] - $getWage*$AgentFactor);
				if($ItemID == 15)
					return round($result["FundWage"] - $getWage*$FundFactor);
		}		
		
	}

	
	//--------------------------------------------------------
	
	static function FindTafsili($TafsiliType, $TafsiliCode){

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
			ExceptionHandler::PushException("تفصیلی مربوطه یافت نشد.[" . $TafsiliType . "-" .  $TafsiliCode . "]");
			return false;
		}

		return array("TafsiliID" => $dt[0]["TafsiliID"], "TafsiliDesc" => $dt[0]["TafsiliDesc"]);
	}

	const Tafsili_LoanPersonID = 1;
	const Tafsili_ReqPersonID = 2;
	const Tafsili_LoanID = 3;
	
	static function GetTafsilis($tafsili, $params){
		
		switch($tafsili)
		{
			case self::Tafsili_LoanPersonID : //وام گیرنده
				$ReqObj = $params[0];
				/* @var $ReqObj LON_requests */
				$res = self::FindTafsili(TAFSILITYPE_PERSON, $ReqObj->LoanPersonID);
				return array(TAFTYPE_PERSONS, $res["TafsiliID"], $res["TafsiliDesc"]);
				
			case self::Tafsili_ReqPersonID : //سرمایه گذار
				$ReqObj = $params[0];
				/* @var $ReqObj LON_requests */
				$res = self::FindTafsili(TAFSILITYPE_PERSON, $ReqObj->ReqPersonID);
				return array(TAFTYPE_PERSONS, $res["TafsiliID"], $res["TafsiliDesc"]);
				
			case self::Tafsili_LoanID : //نوع وام
				$ReqObj = $params[0];
				/* @var $ReqObj LON_requests */
				$res = self::FindTafsili(TAFSILITYPE_LOAN, $ReqObj->LoanID);
				return array(TAFSILITYPE_LOAN, $res["TafsiliID"], $res["TafsiliDesc"]);
		}
	}
}
