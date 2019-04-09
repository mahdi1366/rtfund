<?php
//---------------------------
// programmer:	Jafarkhani
// create Date: 94.06
//---------------------------

require_once DOCUMENT_ROOT . '/office/dms/dms.class.php';

class LON_requests extends PdoDataAccess{
	
	public $RequestID;
	public $BranchID;
	public $LoanID;
	public $ReqPersonID;
	public $ReqDate;
	public $ReqAmount;
	public $StatusID;
	public $ReqDetails;
	public $BorrowerDesc;
	public $BorrowerID;
	public $BorrowerMobile;
	public $LoanPersonID;
	public $guarantees;
	public $AgentGuarantee;
	public $FundGuarantee;
	public $DocumentDesc;	
	public $IsEnded;
	public $SubAgentID;
	public $IsFree;
	public $imp_VamCode;
	public $PlanTitle;
	public $RuleNo;
	public $FundRules;
	
	public $_LoanDesc;
	public $_LoanPersonFullname;
	public $_ReqPersonFullname;
	public $_BranchName;
	public $_SubAgentDesc;
	
	function __construct($RequestID = "") {
		
		if($RequestID != "")
			PdoDataAccess::FillObject ($this, "
				select r.* , concat_ws(' ',p1.fname,p1.lname,p1.CompanyName) _LoanPersonFullname, LoanDesc _LoanDesc,
						concat_ws(' ',p2.fname,p2.lname,p2.CompanyName) _ReqPersonFullname, b.BranchName _BranchName,
						SubDesc as _SubAgentDesc
						
					from LON_requests r 
					left join BSC_persons p1 on(p1.PersonID=LoanPersonID)
					left join LON_loans using(LoanID)
					left join BSC_persons p2 on(p2.PersonID=ReqPersonID)
					left join BSC_branches b using(BranchID)
					left join BSC_SubAgents sa on(SubID=SubAgentID)
				where RequestID=?", array($RequestID));
	}
	
	static function SelectAll($where = "", $param = array()){
		
		return PdoDataAccess::runquery_fetchMode("
			select r.*,l.*,p.PartID,
				concat_ws(' ',p1.fname,p1.lname,p1.CompanyName) ReqFullname,
				concat_ws(' ',p2.fname,p2.lname,p2.CompanyName) LoanFullname,
				bi.InfoDesc StatusDesc,
				BranchName
			from LON_requests r
			join LON_ReqParts p on(r.RequestID=p.RequestID AND IsHistory='NO')
			left join LON_loans l using(LoanID)
			left join BSC_branches using(BranchID)
			left join BaseInfo bi on(bi.TypeID=5 AND bi.InfoID=StatusID)
			left join BSC_persons p1 on(p1.PersonID=r.ReqPersonID)
			left join BSC_persons p2 on(p2.PersonID=r.LoanPersonID)
			where " . $where, $param);
	}
	
	function CheckForDuplicate(){
		
		if(!empty($this->RequestID))
		{
			$dt = PdoDataAccess::runquery("
			select r2.RequestID from LON_requests r1
				join LON_requests r2 on(r1.RequestID<>r2.RequestID 
					AND substr(r1.ReqDate,1,10)=substr(r2.ReqDate,1,10) AND r1.ReqAmount=r2.ReqAmount 
					AND (if(r1.LoanPersonID>0,r1.LoanPersonID=r2.LoanPersonID,1=0) 
						OR if(r1.BorrowerID<>'',r1.BorrowerID=r2.BorrowerID,1=0) 
						OR if(r1.BorrowerDesc<>'',r1.BorrowerDesc=r2.BorrowerDesc,1=0) ) )
			where r1.RequestID=?",array($this->RequestID));
			
			if(count($dt) > 0)
			{
				ExceptionHandler::PushException("در این تاریخ و با این مبلغ وام دیگری با شماره" .
					$dt[0][0]. " ثبت شده است ");
				return false;
			}
		}
		else
		{
			$dt = PdoDataAccess::runquery("
			select r2.RequestID from LON_requests r2 
			where substr(r2.ReqDate,1,10)=substr(now(),1,10) 
				AND r2.ReqAmount=:a
				AND (if(:pid > 0,r2.LoanPersonID=:pid,1=0) OR r2.BorrowerID=:bid OR r2.BorrowerDesc=:bdesc)",
				array(":a" => $this->ReqAmount, ":pid" => $this->LoanPersonID, 
					":bid" => $this->BorrowerID, ":bdesc" => $this->BorrowerDesc));
			
			if(count($dt) > 0)
			{
				ExceptionHandler::PushException("در این تاریخ و با این مبلغ وام دیگری با شماره" .
					$dt[0][0]. " ثبت شده است ");
				return false;
			}
		}
		return true;
	}
	
	function AddRequest($pdo = null){
		$this->ReqDate = PDONOW;
		
		if(!$this->CheckForDuplicate())
			return false;
		
	 	if(!parent::insert("LON_requests",$this, $pdo))
			return false;
		$this->RequestID = parent::InsertID($pdo);
		
		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_add;
		$daObj->MainObjectID = $this->RequestID;
		$daObj->TableName = "LON_requests";
		$daObj->execute($pdo);
		return true;
	}
	
	function EditRequest($pdo = null, $CheckDuplicate = true){
		
		/*if($CheckDuplicate)
			if(!$this->CheckForDuplicate())
				return false;*/
		
	 	if( parent::update("LON_requests",$this," RequestID=:l", array(":l" => $this->RequestID), $pdo) === false )
	 		return false;

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_update;
		$daObj->MainObjectID = $this->RequestID;
		$daObj->TableName = "LON_requests";
		$daObj->execute($pdo);
	 	return true;
    }
	
	static function DeleteRequest($RequestID){
		
		$obj = new LON_requests($RequestID);
		if($obj->StatusID != "1")
		{
			ExceptionHandler::PushException("درخواست در حال گردش قابل حذف نمی باشد");
			return false;
		}
		
		if(!DMS_documents::DeleteAllDocument($RequestID, "loan"))
		{
			ExceptionHandler::PushException("خطا در حذف مدارک");
	 		return false;
		}		
		
		if( parent::delete("LON_ReqParts"," RequestID=?", array($RequestID)) === false )
		{
			ExceptionHandler::PushException("خطا در حذف شرایط");
	 		return false;
		}
		if( parent::delete("LON_installments"," RequestID=?", array($RequestID)) === false )
		{
			ExceptionHandler::PushException("خطا در حذف شرایط");
	 		return false;
		}
		if( parent::delete("LON_requests"," RequestID=?", array($RequestID)) === false )
	 	{
			ExceptionHandler::PushException("خطا در حذف درخواست");
			return false;
		}

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_delete;
		$daObj->MainObjectID = $RequestID;
		$daObj->TableName = "LON_requests";
		$daObj->execute();
	 	return true;
	}
	
	//-------------------------------------
	static function ComputeWage($PartAmount, $CustomerWagePercent, $InstallmentCount, $IntervalType, $PayInterval){
	
		if($PayInterval == 0)
			return 0;

		if($CustomerWagePercent == 0)
			return 0;

		if($IntervalType == "DAY")
			$PayInterval = $PayInterval/30;

		$R = ($CustomerWagePercent/12)*$PayInterval;
		$F7 = $PartAmount;
		$F9 = $InstallmentCount;
		return ((($F7*$R*pow(1+$R,$F9))/(pow(1+$R,$F9)-1))*$F9)-$F7;
	}

	static function YearWageCompute($PartObj, $TotalWage, $YearMonths){

		/*@var $PartObj LON_ReqParts */

		$startDate = DateModules::miladi_to_shamsi($PartObj->PartDate);
		$startDate = DateModules::AddToJDate($startDate, $PartObj->DelayDays, $PartObj->DelayMonths); 
		$startDate = preg_split('/[\-\/]/',$startDate);
		$PayMonth = $startDate[1]*1;

		$FirstYearInstallmentCount = floor((12 - $PayMonth)/(12/$YearMonths));
		$FirstYearInstallmentCount = $PartObj->InstallmentCount < $FirstYearInstallmentCount ? 
				$PartObj->InstallmentCount : $FirstYearInstallmentCount;
		$MidYearInstallmentCount = floor(($PartObj->InstallmentCount-$FirstYearInstallmentCount) / $YearMonths);
		$MidYearInstallmentCount = $MidYearInstallmentCount < 0 ? 0 : $MidYearInstallmentCount;
		$LastYeatInstallmentCount = ($PartObj->InstallmentCount-$FirstYearInstallmentCount) % $YearMonths;
		$LastYeatInstallmentCount = $LastYeatInstallmentCount < 0 ? 0 : $LastYeatInstallmentCount;
		$F9 = $PartObj->InstallmentCount*(12/$YearMonths);

		$yearNo = 1;
		$StartYear = $startDate[0]*1;
		$returnArr = array();
		while(true)
		{
			if($yearNo > $MidYearInstallmentCount+2)
				break;

			$BeforeMonths = 0;
			if($yearNo == 2)
				$BeforeMonths = $FirstYearInstallmentCount;
			else if($yearNo > 2)
				$BeforeMonths = $FirstYearInstallmentCount + ($yearNo-2)*$YearMonths;

			$curMonths = $FirstYearInstallmentCount;
			if($yearNo > 1 && $yearNo <= $MidYearInstallmentCount+1)
				$curMonths = $YearMonths;
			else if($yearNo > $MidYearInstallmentCount+1)
				$curMonths = $LastYeatInstallmentCount;

			$BeforeMonths = $BeforeMonths*(12/$YearMonths);
			$curMonths = $curMonths*(12/$YearMonths);

			$val = (((($F9-$BeforeMonths)*($F9-$BeforeMonths+1))-
				($F9-$BeforeMonths-$curMonths)*($F9-$BeforeMonths-$curMonths+1)))/($F9*($F9+1))*$TotalWage;

			$returnArr[ $StartYear ] = $val;
			$StartYear++;
			$yearNo++;
		}

		return $returnArr;
	}
	
	static function Tanzil($amount, $wage, $Date, $StartDate)
	{
		$Date = DateModules::miladi_to_shamsi($Date);
		$StartDate = DateModules::miladi_to_shamsi($StartDate);
		$days = DateModules::JDateMinusJDate($Date, $StartDate)+1;

		return $amount/(1+($wage*$days/36500));
	}
	//-------------------------------------
	static function GetFirstPayDate($RequestID){
		
		$dt = PdoDataAccess::runquery("select * from LON_payments where RequestID=? order by PayDate",
				array($RequestID));
		if(count($dt) == 0)
		{
			$obj = LON_ReqParts::GetValidPartObj($RequestID);
			return $obj->PartDate;
		}
		
		return $dt[0]["PayDate"];
	}
	
	static function ComputeInstallment_TanzilFormula($partObj, $installmentArray, $ComputeDate = "", 
			$ComputeWage = 'YES'){

		/*@var $partObj LON_ReqParts */
		
		if(!empty($ComputeDate))
		{
			$dt = LON_requests::GetPureAmount($partObj->RequestID, null, null, 
					DateModules::shamsi_to_miladi($ComputeDate, "-"));
			$amount = $dt["PureAmount"];
		}
		else
		{
			$FirstPay = self::GetFirstPayDate($partObj->RequestID);
			$ComputeDate = DateModules::miladi_to_shamsi($FirstPay);
			$partObj->FirstTotalWage = 0;
			$amount = self::GetPurePayedAmount($partObj->RequestID, $partObj, true);
			if($amount === false)
			{
				return false;
			}
			$TotalPureAmount = $amount;
			
		}
		//------------- compute percents of each installment amount ----------------
		$zarib = 1;
		$sum = 0;
		$totalZarib = 0;
		$startDate = $ComputeDate;
		for($i=0; $i<count($installmentArray);$i++)
		{
			$days = DateModules::JDateMinusJDate($installmentArray[$i]["InstallmentDate"],$startDate);
			$zarib = $zarib*(1 + ($partObj->CustomerWage/36500)*$days);
			
			if($ComputeWage == "YES")
			{
				if($installmentArray[$i]["InstallmentAmount"]*1 == 0 && $i < count($installmentArray)-1)
					$percent = 1;
				else
				{
					if($i < count($installmentArray)-1)
					{
						$percent = round($installmentArray[$i]["InstallmentAmount"]*1/$partObj->PartAmount, 2);
						$sum += round($installmentArray[$i]["InstallmentAmount"]*1/$partObj->PartAmount, 2);
					}
					else
						$percent = 1-$sum;
				}
				$installmentArray[$i]["percent"] = $percent;
				$totalZarib += $percent/$zarib;
			}
			else
			{
				if($i < count($installmentArray)-1)
				{
					$amount -= $installmentArray[$i]["InstallmentAmount"]/$zarib;
				}
			}
			$startDate = $installmentArray[$i]["InstallmentDate"];
		}

		if($ComputeWage == "YES")
			$x = round($amount/$totalZarib);
		else
			$x = round($amount*$zarib);

		//-------  update installment Amounts ------------
		$TotalAmount = 0;
		for($i=0; $i<count($installmentArray);$i++)
		{
			if($ComputeWage == "YES")
				$installmentAmount = $x*$installmentArray[$i]["percent"];
			else if($i == count($installmentArray)-1)
				$installmentAmount = $x;

			$TotalAmount += $installmentAmount;
		}
		$TotalWage = $TotalAmount - $TotalPureAmount;
		
		//-------- compute again if installment wage payed at first ------------
		if($partObj->WageReturn == "CUSTOMER" || $partObj->AgentReturn == "CUSTOMER")
		{
			$fundZarib = $partObj->FundWage/$partObj->CustomerWage;
			$agentZarib = ($partObj->CustomerWage-$partObj->FundWage)/$partObj->CustomerWage;
			
			if($partObj->WageReturn == "CUSTOMER")
				$TotalPureAmount -= $TotalWage*$fundZarib;
			if($partObj->AgentReturn == "CUSTOMER")
				$TotalPureAmount -= $TotalWage*$agentZarib;
			
			$partObj->FirstTotalWage = $TotalWage;
			$partObj->EditPart();
			//---------- compute again --------------
			$zarib = 1;
			$sum = 0;
			$totalZarib = 0;
			$startDate = $ComputeDate;
			$amount = $TotalPureAmount;
			for($i=0; $i<count($installmentArray);$i++)
			{
				$days = DateModules::JDateMinusJDate($installmentArray[$i]["InstallmentDate"],$startDate);
				$zarib = $zarib*(1 + ($partObj->CustomerWage/36500)*$days);

				if($ComputeWage == "YES")
					$totalZarib += $percent/$zarib;
				else if($i < count($installmentArray)-1)
					$amount -= $installmentArray[$i]["InstallmentAmount"]/$zarib;
				$startDate = $installmentArray[$i]["InstallmentDate"];
			}

			if($ComputeWage == "YES")
				$x = round($amount/$totalZarib);
			else
				$x = round($amount*$zarib);
		}
		
		//-------  update installment Amounts ------------
		$TotalAmount = 0;
		for($i=0; $i<count($installmentArray);$i++)
		{
			if($ComputeWage == "YES")
				$installmentArray[$i]["InstallmentAmount"] = $x*$installmentArray[$i]["percent"];
			else if($i == count($installmentArray)-1)
				$installmentArray[$i]["InstallmentAmount"] = $x;

			$TotalAmount += $installmentArray[$i]["InstallmentAmount"];
		}
		$TotalWage = $TotalAmount - $TotalPureAmount;
		
		//------ compute wages of installments -----------
		$difference = 0;
		for($i=0; $i < count($installmentArray); $i++)
		{
			$days = DateModules::JDateMinusJDate($installmentArray[$i]["InstallmentDate"],$ComputeDate);

			$installmentArray[$i]["wage"] = round(($TotalAmount-$TotalWage)*
					(($partObj->CustomerWage/36500)*$days));
			
			$TotalAmount -= $installmentArray[$i]["InstallmentAmount"];
			$TotalWage -= $installmentArray[$i]["wage"];
			$ComputeDate  = $installmentArray[$i]["InstallmentDate"];
		}
		//---------- rounding up installments -------------
		for($i=0; $i < count($installmentArray); $i++)
		{
			if($i < count($installmentArray)-1)
			{
				$a = $installmentArray[$i]["InstallmentAmount"];
				$installmentArray[$i]["InstallmentAmount"] = roundUp($a,-4);
				$difference += $installmentArray[$i]["InstallmentAmount"] - $a;
			}
			else
			{
				$installmentArray[$i]["InstallmentAmount"] -= $difference;
			}	
		}
		//------------------------------------------------	
		return $installmentArray;
	}
	
	static function GetDelayAmounts($RequestID, $PartObj = null){
		
		$PartObj = $PartObj == null ? LON_ReqParts::GetValidPartObj($RequestID) : $PartObj;
		$endDelayDate = DateModules::AddToGDate($PartObj->PartDate, $PartObj->DelayDays*1, $PartObj->DelayMonths*1);
		$DelayDuration = DateModules::GDateMinusGDate($endDelayDate, $PartObj->PartDate)+1;
		
		if($PartObj->DelayPercent == 0)
		{
			return array(
				"CustomerDelay" => 0,
				"FundDelay" => 0,
				"AgentDelay" => 0
			);
		}
		
		$fundZarib = $PartObj->FundWage/$PartObj->DelayPercent;
			
		if($PartObj->ComputeMode == "NEW" )
		{
			
			$CustomerDelay = round($PartObj->PartAmount - 
					self::Tanzil($PartObj->PartAmount, $PartObj->DelayPercent, $endDelayDate, $PartObj->PartDate));
			$FundDelay = round($fundZarib*$CustomerDelay);
			$AgentDelay = $CustomerDelay - round($fundZarib*$CustomerDelay);
		}
		else
		{
			if($PartObj->DelayDays*1 > 0)
			{
				$CustomerDelay = round($PartObj->PartAmount*$PartObj->DelayPercent*$DelayDuration/36500);
				$FundDelay = round($PartObj->PartAmount*$PartObj->FundWage*$DelayDuration/36500);
				$AgentDelay = round($PartObj->PartAmount*($PartObj->DelayPercent - $PartObj->FundWage)*$DelayDuration/36500);		
			}
			else
			{
				$CustomerDelay = round($PartObj->PartAmount*$PartObj->DelayPercent*$PartObj->DelayMonths/1200);
				$FundDelay = round($PartObj->PartAmount*$PartObj->FundWage*$PartObj->DelayMonths/1200);
				$AgentDelay = round($PartObj->PartAmount*($PartObj->DelayPercent - $PartObj->FundWage)*$PartObj->DelayMonths/1200);
			}
		}
		return array(
			"CustomerDelay" => $CustomerDelay,
			"FundDelay" => $FundDelay,
			"AgentDelay" => $AgentDelay
		);
	}
	
	static function GetWageAmounts($RequestID, $PartObj = null){
		
		$PartObj = $PartObj == null ? LON_ReqParts::GetValidPartObj($RequestID) : $PartObj;
		$PartObj->MaxFundWage = $PartObj->MaxFundWage*1;
		
		$MaxWage = max($PartObj->CustomerWage*1 , $PartObj->FundWage);
		$CustomerFactor =	$MaxWage == 0 ? 0 : $PartObj->CustomerWage/$MaxWage;
		$FundFactor =		$MaxWage == 0 ? 0 : $PartObj->FundWage/$MaxWage;
		$AgentFactor =		$MaxWage == 0 ? 0 : ($PartObj->CustomerWage-$PartObj->FundWage)/$MaxWage;
		
		if($PartObj->PayInterval > 0)
			$YearMonths = ($PartObj->IntervalType == "DAY" ) ? 
				floor(365/$PartObj->PayInterval) : 12/$PartObj->PayInterval;
		else
			$YearMonths = 12;
		
		$TotalWage = 0;
		if($PartObj->ComputeMode == "NEW")
		{
			$dt = LON_installments::GetValidInstallments($PartObj->RequestID);
			if(count($dt)>0)
				foreach($dt as $row)
					$TotalWage += $row["wage"]*1;
		}
		else
		{
			$TotalWage = round(self::ComputeWage($PartObj->PartAmount, $MaxWage/100, 
					$PartObj->InstallmentCount, 
					$PartObj->IntervalType, $PartObj->PayInterval));	
		}
		
		//...................................
		if($PartObj->MaxFundWage > 0)
		{
			if($PartObj->WageReturn == "INSTALLMENT")
				$FundYears = self::YearWageCompute($PartObj, $PartObj->MaxFundWage, $YearMonths);
			else 
				$FundYears = array();
		}	
		else
		{
			$years = self::YearWageCompute($PartObj, $TotalWage*1, $YearMonths);
			$FundYears = array();
			foreach($years as $year => $amount)
				$FundYears[$year] = round($FundFactor*$amount);
		}	
		
		$AgentYears = array();
		foreach($years as $year => $amount)
			$AgentYears[$year] = round($amount - $FundYears[$year]);
		//...................................
		
		$FundWage = $PartObj->MaxFundWage > 0 ? $PartObj->MaxFundWage : round($TotalWage*$FundFactor);
		$AgentWage = $PartObj->MaxFundWage > 0 ? $TotalWage - $PartObj->MaxFundWage : round($TotalWage*$AgentFactor);
		
		return array(
			"FundWage" => $FundWage,
			"AgentWage" => $AgentWage,
			"CustomerWage" => round($TotalWage*$CustomerFactor),
			"FundWageYears" => $FundYears,
			"AgentWageYears" => $AgentYears
		);
	}
	//-------------------------------------
	
	static function NewComputePayments($RequestID, &$installments, $pdo = null){

		$installments = PdoDataAccess::runquery("
			select * from LON_installments 
			where RequestID=? AND history='NO' AND IsDelayed='NO'
			order by InstallmentDate", 
			array($RequestID), $pdo);
		
		$refInstallments = array();
		for($i=0; $i<count($installments); $i++)
			$refInstallments[ $installments[$i]["InstallmentID"] ] = &$installments[$i];
		
		$obj = LON_ReqParts::GetValidPartObj($RequestID);

		$returnArr = array();
		$records = PdoDataAccess::runquery("
			select * from (
				select InstallmentID id,0 BackPayID,'installment' type, 
				InstallmentDate RecordDate,InstallmentAmount RecordAmount,0 PayType, '' details, wage
				from LON_installments where RequestID=:r AND history='NO' AND IsDelayed='NO'
			union All
				select 0 id,BackPayID, 'pay' type, substr(p.PayDate,1,10) RecordDate, PayAmount RecordAmount, PayType,
					if(PayType=" . BACKPAY_PAYTYPE_CORRECT . ",p.details,'') details,0
				from LON_BackPays p
				left join ACC_IncomeCheques i using(IncomeChequeID)
				left join BaseInfo bi on(bi.TypeID=6 AND bi.InfoID=p.PayType)
				where RequestID=:r AND 
					if(p.PayType=".BACKPAY_PAYTYPE_CHEQUE.",i.ChequeStatus=".INCOMECHEQUE_VOSUL.",1=1)
			union All
				select 0 id,0 BackPayID,'pay' type, CostDate RecordDate, -1*CostAmount RecordAmount,0, CostDesc details, 0
				from LON_costs 
				where RequestID=:r AND CostAmount<>0
			)t
			order by substr(RecordDate,1,10), RecordAmount desc" , array(":r" => $RequestID), $pdo);
		
		$ComputePayRows = array();
		$tempForReturnArr = array();
		for($i=0; $i < count($records); $i++)
		{
			$record = $records[$i];
			$x = $tempForReturnArr;
			$tempForReturnArr = array(
					"InstallmentID" => $record["id"],
					"BackPayID" => $record["BackPayID"],
					"details" => $record["details"],
					"ActionType" => $record["type"],
					"ActionDate" => $record["RecordDate"],
					"ActionAmount" => $record["RecordAmount"]*1,
					"ForfeitDays" => 0,
					
					"pure" => 0,
					"wage" => 0,
					"LateWage" => 0,
					"LateForfeit" => 0,
				
					"totalpure" => count($x)>0 ? $x["totalpure"] : 0,
					"totalwage" => count($x)>0 ? $x["totalwage"] : 0,
					"totalLateWage" => count($x)>0 ? $x["totalLateWage"] : 0,
					"totalLateForfeit" => count($x)>0 ? $x["totalLateForfeit"] : 0,
					"CurForfeitAmount" => 0,
				
					"TotalRemainder" => count($x)>0 ? $x["TotalRemainder"] : 0
				);	
			if($record["type"] == "installment")
			{
				$tempForReturnArr["pure"] = $record["RecordAmount"]*1 - $record["wage"]*1;
				$tempForReturnArr["wage"] = $record["wage"]*1;
				
				$tempForReturnArr["totalpure"] += $tempForReturnArr["pure"];
				$tempForReturnArr["totalwage"] += $tempForReturnArr["wage"];
				
				self::BackPayCompute($obj, $tempForReturnArr, $record, $records, $i);
			}
			if($record["type"] == "pay")
			{
				if($record["PayType"] == BACKPAY_PAYTYPE_CORRECT)
				{
					$tempForReturnArr["TotalRemainder"] += $record["RecordAmount"]*1;
				}
				else
				{
					if($record["RecordAmount"]*1 < 0)
					{
						$tempForReturnArr["TotalRemainder"] += abs($record["RecordAmount"]*1);
						$record["RecordAmount"] = 0;
					}
					self::BackPayCompute($obj, $tempForReturnArr, $record, $records, $i);
					$tempForReturnArr["TotalRemainder"] -= $record["RecordAmount"]*1;
				}
			}
			
			$StartDate = $record["RecordDate"];
			$ToDate = $i+1 < count($records) ? $records[$i+1]["RecordDate"] : DateModules::Now();
			if($ToDate > DateModules::Now())
				$ToDate = DateModules::Now();
			if($StartDate < $ToDate)
			{
				if($tempForReturnArr["totalpure"] > 0)
				{
					$forfeitDays = DateModules::GDateMinusGDate($ToDate,$StartDate);
					$tempForReturnArr["ForfeitDays"] = $forfeitDays;
					
					$forgivePercent = $obj->ForgivePercent*1;
					$ForfeitPercent = $obj->ForfeitPercent - min($obj->ForfeitPercent, $forgivePercent);
					$forgivePercent -= min($obj->ForfeitPercent, $forgivePercent);
					$LatePercent = $obj->LatePercent;
					if($forgivePercent > 0)
						$LatePercent = $obj->LatePercent - $forgivePercent;
					if($LatePercent < 0)
						$LatePercent = 0;
							
					$LateWage = round($tempForReturnArr["totalpure"]*$LatePercent*$forfeitDays/36500);
					$LateForfeit = round($tempForReturnArr["totalpure"]*$ForfeitPercent*$forfeitDays/36500);
					
					$tempForReturnArr["LateWage"] = $LateWage;
					$tempForReturnArr["totalLateWage"] += $LateWage;
					
					$tempForReturnArr["LateForfeit"] = $LateForfeit;
					$tempForReturnArr["totalLateForfeit"] += $LateForfeit;
					
					$tempForReturnArr["TotalRemainder"] += $LateWage + $LateForfeit;
				}
			}

			$tempForReturnArr["pays"] = array();
			$returnArr[] = $tempForReturnArr;
			
			if($record["type"] == "pay" && $tempForReturnArr["ActionAmount"] > 0)
				$ComputePayRows[] = $tempForReturnArr;
			
			if($record["type"] == "installment")
			{
				$refInstallments[ $record["id"] ]["ForfeitDays"] = $tempForReturnArr["ForfeitDays"];
				$refInstallments[ $record["id"] ]["CurForfeitAmount"] = $tempForReturnArr["CurForfeitAmount"];
				$refInstallments[ $record["id"] ]["remainder"] = min(
						$refInstallments[ $record["id"] ]["InstallmentAmount"],
						$tempForReturnArr["TotalRemainder"]);
				$refInstallments[ $record["id"] ]["TotalRemainder"] = $tempForReturnArr["TotalRemainder"];
			}
		}

		//............. pay rows of each installment ..............
		$payIndex = 0;
		while(true)
		{
			$PayRecord = $payIndex < count($ComputePayRows) ? $ComputePayRows[$payIndex++] : null;
			if(!$PayRecord || $PayRecord["ActionAmount"] > 0)
				break;
		}
		
		for($i=0; $i < count($returnArr); $i++)
		{
			$InstallmentRow = &$returnArr[$i];
			if($InstallmentRow["ActionType"] != "installment")
				continue;
			
			$amount = $InstallmentRow["ActionAmount"]*1;
			
			while($amount > 0)
			{
				$StartDate = count($InstallmentRow["pays"]) > 0 ?
						$InstallmentRow["pays"][count($InstallmentRow["pays"])-1]["G-PayedDate"] :
						$InstallmentRow["ActionDate"];
				if($InstallmentRow["ActionDate"] > $StartDate)
					$StartDate = $InstallmentRow["ActionDate"];
				if(!$PayRecord)
					$StartDate = $InstallmentRow["ActionDate"];
				
				$ToDate = $PayRecord ? $PayRecord["ActionDate"] : DateModules::Now();
				if($ToDate > DateModules::Now())
					$ToDate = DateModules::Now();
				$forfeitDays = DateModules::GDateMinusGDate($ToDate,$StartDate);
				
				$ForfeitPercent = $obj->ForfeitPercent - min($obj->ForfeitPercent, $obj->ForgivePercent);
				$CurForfeit = round($amount*$ForfeitPercent*$forfeitDays/36500);
				
				if($CurForfeit < 0)
				{
					$forfeitDays = 0;
					$CurForfeit = 0;
				}
				
				$SavePayedAmount = $PayRecord ? $PayRecord["ActionAmount"] : 0;
				$SaveAmount = $amount;
				if($PayRecord && $obj->PayCompute == "forfeit")
					$PayRecord["ActionAmount"] -= min($CurForfeit,$PayRecord["ActionAmount"]);
							
				$payAmount = $PayRecord ? $PayRecord["ActionAmount"] : $amount;
				
				$min = min($amount,$payAmount);
				$amount -= $min;
				if($PayRecord)
					$PayRecord["ActionAmount"] -= $min;
				
				$InstallmentRow["pays"][] = array(
					"ForfeitDays" => $forfeitDays,
					"forfeit" => $CurForfeit,
					"remain" => $PayRecord ? $amount : $SaveAmount ,
					"G-PayedDate" => $PayRecord ? $PayRecord["ActionDate"] : '',
					"PayedDate" => $PayRecord ? DateModules::miladi_to_shamsi($PayRecord["ActionDate"]) : '',
					"PayedAmount" => number_format($SavePayedAmount)
				);
				if($PayRecord && $PayRecord["ActionAmount"] == 0)
				{
					$PayRecord = $payIndex < count($ComputePayRows) ? $ComputePayRows[$payIndex++] : null;
				}
			}
		}
			
		//.........................................................
		
		return $returnArr;
	}
	
	static function BackPayCompute($partObj, &$returnArr, &$curRecord, $records, $index){
		
		$totalRemain = $returnArr["TotalRemainder"];
		$RecordAmount = $curRecord["RecordAmount"];
		
		if($curRecord["type"] == "installment")
		{
			if($returnArr["TotalRemainder"] >= 0)
			{
				$returnArr["TotalRemainder"] += $curRecord["RecordAmount"]*1;
				return;
			}
			$temp = $returnArr["TotalRemainder"];
			$returnArr["TotalRemainder"] += $curRecord["RecordAmount"]*1;
			$curRecord["RecordAmount"] = abs($temp);
		}
		
		//---------------- base on percent ------------------
		if($totalRemain == 0) // pay is first record
		{
			$curRecord = $records[$index+1];
			$totalRemain = $curRecord["RecordAmount"]*1;
			$share = $curRecord["RecordAmount"]*1 - $curRecord["wage"];
			$share_pure = round($RecordAmount*($share/$totalRemain));
			$share_wage = round($RecordAmount*($curRecord["wage"]/$totalRemain));
			$share_LateWage = 0;
			$share_LateForfeit = 0;
		}
		else
		{
			if($curRecord["type"] == "installment" && $RecordAmount > 0)
			{
				$totalRemain = abs($totalRemain);
				$share_pure = round($totalRemain*($returnArr["totalpure"]/$RecordAmount));
				$share_wage = round($totalRemain*($returnArr["totalwage"]/$RecordAmount));
				$share_LateWage = round($totalRemain*($returnArr["totalLateWage"]/$RecordAmount));
				$share_LateForfeit = $returnArr["totalLateWage"] == 0 ? 0 :
						$totalRemain - $share_pure - $share_wage - $share_LateWage;
			}
			else
			{
				$share_pure = round($RecordAmount*($returnArr["totalpure"]/$totalRemain));
				$share_wage = round($RecordAmount*($returnArr["totalwage"]/$totalRemain));
				$share_LateWage = round($RecordAmount*($returnArr["totalLateWage"]/$totalRemain));
				$share_LateForfeit = $returnArr["totalLateWage"] == 0 ? 0 :
						$curRecord["RecordAmount"] - $share_pure - $share_wage - $share_LateWage;
			}
		}
		
		$returnArr["share_pure"] = $share_pure;
		$returnArr["share_wage"] = $share_wage;
		$returnArr["share_LateWage"] = $share_LateWage;
		$returnArr["share_LateForfeit"] = $share_LateForfeit;
		
		$returnArr["totalpure"] -= min($share_pure,$returnArr["totalpure"]);
		$returnArr["totalwage"] -= min($share_wage,$returnArr["totalwage"]);
		$returnArr["totalLateWage"] -= min($share_LateWage,$returnArr["totalLateWage"]);
		$returnArr["totalLateForfeit"] -= min($share_LateForfeit,$returnArr["totalLateForfeit"]);
		return;
		//------ base on pure/wage/forfeit order
		for($i=1; $i<=4; $i++)
		{
			$min = min($returnArr["total" . $partObj->{"_param" . $i}], $curRecord["RecordAmount"]*1);
			$curRecord["RecordAmount"] -= $min;
			$returnArr["total" . $partObj->{"_param" . $i}] -= $min;
			if($curRecord["RecordAmount"] == 0)
				return;
			if($partObj->{"_param" . $i} == "pure")
			{
				for($j=$index+1; $j<count($records); $j++)
				{
					if($records[$j]["type"] == "installment")
						return;
				}
			}
		}
	}
	
	static function ComputePayments($RequestID, &$installments, $pdo = null){

		$obj = LON_ReqParts::GetValidPartObj($RequestID);
		if($obj->ComputeMode == "NEW")
			return self::NewComputePayments($RequestID, $installments, $pdo);
		
		$installments = PdoDataAccess::runquery("select * from 
			LON_installments where RequestID=? AND history='NO' AND IsDelayed='NO' order by InstallmentDate", 
			array($RequestID), $pdo);
		
		$refInstallments = array();
		for($i=0; $i<count($installments); $i++)
		{
			$installments[$i]["remainder"] = $installments[$i]["InstallmentAmount"];
			$refInstallments[ $installments[$i]["InstallmentID"] ] = &$installments[$i];			
		}

		$returnArr = array();
		$records = PdoDataAccess::runquery("
			select * from (
				select InstallmentID id,'installment' type, 0 BackPayID,
				InstallmentDate RecordDate,InstallmentAmount RecordAmount,0 PayType, '' details, wage
				from LON_installments where RequestID=:r AND history='NO' AND IsDelayed='NO'
			union All
				select BackPayID id, 'pay' type, BackPayID
					substr(p.PayDate,1,10) RecordDate, PayAmount RecordAmount, PayType,
					if(PayType=" . BACKPAY_PAYTYPE_CORRECT . ",p.details,'') details,0
				from LON_BackPays p
				left join ACC_IncomeCheques i using(IncomeChequeID)
				left join BaseInfo bi on(bi.TypeID=6 AND bi.InfoID=p.PayType)
				where RequestID=:r AND 
					if(p.PayType=".BACKPAY_PAYTYPE_CHEQUE.",i.ChequeStatus=".INCOMECHEQUE_VOSUL.",1=1)
			union All
				select 0 id,'pay' type, 0 BackPayID
					CostDate RecordDate, -1*CostAmount RecordAmount,0, CostDesc details, 0
				from LON_costs 
				where RequestID=:r AND CostAmount<>0
			)t
			order by substr(RecordDate,1,10), RecordAmount desc" , array(":r" => $RequestID), $pdo);
		
		$TotalForfeit = 0;
		$TotalRemainder = 0;
		$ComputePayRows = array();
		for($i=0; $i < count($records); $i++)
		{
			$record = $records[$i];
			$tempForReturnArr = array(
					"InstallmentID" => $record["id"],
					"BackPayID" => $record["BackPayID"],
					"details" => $record["details"],
					"ActionType" => $record["type"],
					"ActionDate" => $record["RecordDate"],
					"ActionAmount" => $record["RecordAmount"]*1,
					"ForfeitDays" => 0,
					"CurForfeitAmount" => 0,
					"ForfeitAmount" => $TotalForfeit,
					"TotalRemainder" => $TotalRemainder
				);	
			if($record["type"] == "installment")
			{
				$TotalRemainder += $record["RecordAmount"]*1;
			}
			if($record["type"] == "pay")
			{
				if($record["PayType"] == BACKPAY_PAYTYPE_CORRECT)
				{
					$TotalRemainder -= $record["RecordAmount"]*1;
				}
				else
				{
					if($record["RecordAmount"]*1 < 0)
					{
						$TotalRemainder += abs($record["RecordAmount"]*1);
						$record["RecordAmount"] = 0;
					}
					if($obj->PayCompute == "installment")
					{
						$min = min($TotalRemainder, $record["RecordAmount"]*1);
						$TotalRemainder -= $min;
						$record["RecordAmount"] -= $min;
						if($record["RecordAmount"] > 0)
						{
							$IsInstallmentAfter = false;
							for($j=$i+1; $j<count($records); $j++)
							{
								if($records[$j]["type"] == "installment")
								{
									$IsInstallmentAfter = true;
									break;
								}
							}
							if($IsInstallmentAfter)
							{
								$TotalRemainder -= $record["RecordAmount"];
							}
							else
							{
								$TotalForfeit -= $record["RecordAmount"];
								if($TotalForfeit < 0)
								{
									$TotalRemainder += $TotalForfeit;
									$TotalForfeit = 0;
								}
							}							
						}
					}
					else
					{
						$min = min($TotalForfeit, $record["RecordAmount"]*1);
						$TotalForfeit -= $min;
						$record["RecordAmount"] -= $min;
						if($record["RecordAmount"] > 0)
						{
							$TotalRemainder -= $record["RecordAmount"]*1;
						}
					}				
				}
			}
			
			$StartDate = $record["RecordDate"];
			$ToDate = $i+1 < count($records) ? $records[$i+1]["RecordDate"] : DateModules::Now();
			if($ToDate > DateModules::Now())
				$ToDate = DateModules::Now();
			if($StartDate < $ToDate && $TotalRemainder > 0)
			{
				if($TotalRemainder > 0)
				{
					$forfeitDays = DateModules::GDateMinusGDate($ToDate,$StartDate);
					$percent = $obj->ForfeitPercent*1 + $obj->LatePercent*1 - $obj->ForgivePercent*1;
					$percent = $percent < 0 ? 0 : $percent;
					$CurForfeit = round($TotalRemainder*$percent*$forfeitDays/36500);
					$tempForReturnArr["ForfeitDays"] = $forfeitDays;
					$tempForReturnArr["CurForfeitAmount"] = $CurForfeit;
					$TotalForfeit += $CurForfeit;
				}
			}

			$tempForReturnArr["TotalRemainder"] = $TotalRemainder;
			$tempForReturnArr["ForfeitAmount"] = $TotalForfeit;
			
			$tempForReturnArr["pays"] = array();
			$returnArr[] = $tempForReturnArr;
			
			if($record["type"] == "pay" && $tempForReturnArr["ActionAmount"] > 0)
				$ComputePayRows[] = $tempForReturnArr;
			
			if($record["type"] == "installment")
			{
				$refInstallments[ $record["id"] ]["ForfeitDays"] = $tempForReturnArr["ForfeitDays"];
				$refInstallments[ $record["id"] ]["CurForfeitAmount"] = $tempForReturnArr["CurForfeitAmount"];
				$refInstallments[ $record["id"] ]["TotalRemainder"] = $tempForReturnArr["TotalRemainder"];
			}
		}
		//............. pay rows of each installment ..............
		$payIndex = 0;
		while(true)
		{
			$PayRecord = $payIndex < count($ComputePayRows) ? $ComputePayRows[$payIndex++] : null;
			if(!$PayRecord || $PayRecord["ActionAmount"] > 0)
				break;
		}
		
		for($i=0; $i < count($returnArr); $i++)
		{
			$InstallmentRow = &$returnArr[$i];
			if($InstallmentRow["ActionType"] != "installment")
				continue;
			
			$amount = $InstallmentRow["ActionAmount"]*1;
			
			while($amount > 0)
			{
				$StartDate = count($InstallmentRow["pays"]) > 0 ?
						$InstallmentRow["pays"][count($InstallmentRow["pays"])-1]["G-PayedDate"] :
						$InstallmentRow["ActionDate"];
				if($InstallmentRow["ActionDate"] > $StartDate)
					$StartDate = $InstallmentRow["ActionDate"];
				//if(!$PayRecord)
				//	$StartDate = $InstallmentRow["ActionDate"];
				
				$ToDate = $PayRecord ? $PayRecord["ActionDate"] : DateModules::Now();
				if($ToDate > DateModules::Now())
					$ToDate = DateModules::Now();
				$forfeitDays = DateModules::GDateMinusGDate($ToDate,$StartDate);
				$percent = $obj->ForfeitPercent*1 + $obj->LatePercent*1 - $obj->ForgivePercent*1;
				$percent = $percent < 0 ? 0 : $percent;
				$CurForfeit = round($amount*$percent*$forfeitDays/36500);
				if($CurForfeit < 0)
				{
					$forfeitDays = 0;
					$CurForfeit = 0;
				}
				
				$SavePayedAmount = $PayRecord ? $PayRecord["ActionAmount"] : 0;
				$SaveAmount = $amount;
				if($PayRecord && $obj->PayCompute == "forfeit")
					$PayRecord["ActionAmount"] -= min($CurForfeit,$PayRecord["ActionAmount"]);
							
				$payAmount = $PayRecord ? $PayRecord["ActionAmount"] : $amount;
				
				$min = min($amount,$payAmount);
				$amount -= $min;
				if($PayRecord)
					$PayRecord["ActionAmount"] -= $min;
				
				$InstallmentRow["pays"][] = array(
					"ForfeitDays" => $forfeitDays,
					"forfeit" => $CurForfeit,
					"remain" => $PayRecord ? $amount : $SaveAmount ,
					"G-PayedDate" => $PayRecord ? $PayRecord["ActionDate"] : '',
					"PayedDate" => $PayRecord ? DateModules::miladi_to_shamsi($PayRecord["ActionDate"]) : '',
					"PayedAmount" => number_format($SavePayedAmount)
				);
				
				$refInstallments[ $InstallmentRow["InstallmentID"] ]["remainder"] -= $SavePayedAmount;
				
				if($PayRecord && $PayRecord["ActionAmount"] == 0)
				{
					$PayRecord = $payIndex < count($ComputePayRows) ? $ComputePayRows[$payIndex++] : null;
				}
			}
		}
			
		//.........................................................
		
		return $returnArr;
	}
	
	/**
	 * جدول دوم محسابه مرحله ایی اصل و کارمزد تا انتها که باید به صفر برسد
	 * @param type $RequestID
	 * @return array
	 */
	static function ComputePures($RequestID){
		
		$PartObj = LON_ReqParts::GetValidPartObj($RequestID);
		$temp = LON_installments::GetValidInstallments($RequestID);
		//$totalBackPay = $PartObj->PartAmount;
		$totalBackPay = self::GetPurePayedAmount($RequestID, $PartObj, $PartObj->ComputeMode == "NEW");
		//.............................
		$returnArr = array();
		$returnArr[] = array(
			"InstallmentDate" => "",
			"InstallmentAmount" => 0,
			"wage" => 0,
			"pure" => 0,
			"totalPure" => $totalBackPay
		);
		$totalPure = $totalBackPay;
		$ComputeDate = $PartObj->PartDate;
		for($i=0; $i< count($temp); $i++)
		{
			$prevRow = $i == 0 ? null : $temp[$i-1];
			$row = &$temp[$i];
			
			if($temp[$i]["wage"]*1 > 0)
			{
				$totalPure -= $row["InstallmentAmount"] - $row["wage"];
				$returnArr[] = array(
					"InstallmentDate" => $row["InstallmentDate"],
					"InstallmentAmount" => $row["InstallmentAmount"],
					"wage" => $row["wage"],
					"pure" => $row["InstallmentAmount"] - $row["wage"],
					"totalPure" => $totalPure 
				);
				continue;
			}
			$record = array(
				"InstallmentDate" => $row["InstallmentDate"],
				"InstallmentAmount" => $row["InstallmentAmount"],
				"wage" => 0,
				"pure" => 0,
				"totalPure" => 0 
			);
			//.............................
			/*if($PartObj->PayInterval == 0 || $PartObj->WageReturn != "INSTALLMENT")
				$record["wage"] = 0;
			else
			{*/
				
				//$tanzilAmount = Tanzil($row["InstallmentAmount"], $PartObj->CustomerWage, $row["InstallmentDate"], $PartObj->PartDate);
				//$record["wage"] = $row["InstallmentAmount"]*1 - $tanzilAmount;
				
				/*$V = $totalPure;
				$R = $PartObj->IntervalType == "MONTH" ? 
					1200/$PartObj->PayInterval : 36500/$PartObj->PayInterval;
				$record["wage"] = round( $V*($PartObj->CustomerWage/$R) );*/
			//}
			
			$days = DateModules::GDateMinusGDate($row["InstallmentDate"], $ComputeDate)+1;
			$record["wage"] = round( $totalPure*$PartObj->CustomerWage*$days/36500 );
			
			//.............................
			$totalPure -= $row["InstallmentAmount"] - $record["wage"];
			$ComputeDate = $row["InstallmentDate"];			
			$record["pure"] = $row["InstallmentAmount"] - $record["wage"];
			$record["totalPure"] = $totalPure;
			//.............................
			$returnArr[] = $record;
		}
		
		return $returnArr;

	}
	
	/**
	 * با درنظر گرفتن مراحل پرداخت و پرداخت تنفس در ابتدا یا اقساط این تابع مبلغی را برمی گرداند که در ابتدا
	 * به مشتری پرداخت شده است که اگر طی چند مرحله باشد مراحل دوم به بعد به تاریخ مرحله اول تنزیل می شوند
	 * @param type $RequestID
	 * @param type $PartObj
	 * @param type $TanzilCompute
	 * @return boolean
	 */
	static function GetPurePayedAmount($RequestID, $PartObj = null)
	{
		$PartObj = $PartObj == null ? LON_ReqParts::GetValidPartObj($RequestID) : $PartObj;
		/*@var $PartObj LON_ReqParts */
		
		if($PartObj->ComputeMode != "NEW")
		{
			return $PartObj->PartAmount*1;
		}
		
		$dt = LON_payments::Get(" AND RequestID=?", array($PartObj->RequestID), ' order by PayDate desc');
		$dt = $dt->fetchAll();
		if(count($dt) == 0)
		{
			ExceptionHandler::PushException("مراحل پرداخت را وارد نکرده اید");
			return false;
		}
		/*$amount = $dt[0]["PayAmount"];
		for($i=1; $i<count($dt); $i++)
		{ 
			$amount += self::Tanzil($dt[$i]["PayAmount"], $PartObj->CustomerWage, $dt[$i]["PayDate"], 
					$dt[0]["PayDate"]);
		}*/
		
		$amount = 0;
		for($i=0; $i<count($dt); $i++)
		{
			if($i == count($dt)-1)
			{
				$amount += $dt[$i]["PayAmount"]*1;
				break;
			}
			$amount = self::Tanzil($amount + $dt[$i]["PayAmount"]*1, $PartObj->CustomerWage, $dt[$i]["PayDate"], 
					$dt[$i+1]["PayDate"]);
		}
		
		$result = self::GetDelayAmounts($RequestID, $PartObj);
		if($PartObj->DelayReturn != "INSTALLMENT")
			$amount -= $result["FundDelay"];
		if($PartObj->AgentDelayReturn != "INSTALLMENT")
			$amount -= $result["AgentDelay"];
		
		if($PartObj->FirstTotalWage*1 > 0)
			$amount -= $PartObj->FirstTotalWage*1;
		else if($PartObj->WageReturn == "CUSTOMER" || $PartObj->AgentReturn == "CUSTOMER")
		{
			$result = self::GetWageAmounts($RequestID, $PartObj);
			if($PartObj->WageReturn == "CUSTOMER")
				$amount -= $result["FundWage"];
			if($PartObj->AgentReturn == "CUSTOMER")
				$amount -= $result["AgentWage"];
		}
		
		return round($amount);		
	}
	
	/**
	 مبلغ قابل پرداخت به مشتری
	 * @param type $RequestID
	 * @param type $PartObj
	 * @param type $TanzilCompute
	 * @return boolean
	 */
	static function GetPayedAmount($RequestID, $PartObj = null)
	{
		$PartObj = $PartObj == null ? LON_ReqParts::GetValidPartObj($RequestID) : $PartObj;
		/*@var $PartObj LON_ReqParts */
		
		$amount = $PartObj->PartAmount*1;
		
		$result = self::GetDelayAmounts($RequestID, $PartObj);
		if($PartObj->DelayReturn == "CUSTOMER")
			$amount -= $result["FundDelay"];
		if($PartObj->AgentDelayReturn == "CUSTOMER")
			$amount -= $result["AgentDelay"];
		
		if($PartObj->FirstTotalWage*1 > 0)
			$amount -= $PartObj->FirstTotalWage*1;
		else if($PartObj->WageReturn == "CUSTOMER" || $PartObj->AgentReturn == "CUSTOMER")
		{
			$result = self::GetWageAmounts($RequestID, $PartObj);
			if($PartObj->WageReturn == "CUSTOMER")
				$amount -= $result["FundWage"];
			if($PartObj->AgentReturn == "CUSTOMER")
				$amount -= $result["AgentWage"];
		}
		
		return round($amount);		
	}
	
	/**
	 * loan amount + delay if in installment + wage if in installment
	 */
	static function GetTotalReturnAmount($RequestID, $PartObj = null , $TanzilCompute = false){
		
		$PartObj = $PartObj == null ? LON_ReqParts::GetValidPartObj($RequestID) : $PartObj;
		
		$extraAmount = 0;
		$result = self::GetWageAmounts($RequestID, $PartObj);
		if($PartObj->WageReturn == "INSTALLMENT")
			$extraAmount += $result["FundWage"];
		if($PartObj->AgentReturn == "INSTALLMENT")
			$extraAmount += $result["AgentWage"];
		
		$result = self::GetDelayAmounts($RequestID, $PartObj);
		if($PartObj->DelayReturn == "INSTALLMENT")
			$extraAmount += $result["FundDelay"];
		if($PartObj->AgentDelayReturn == "INSTALLMENT")
			$extraAmount += $result["AgentDelay"];
		
		$amount = self::GetPurePayedAmount($RequestID, $PartObj , $TanzilCompute);		
		return $amount + $extraAmount + $PartObj->FirstTotalWage;		
	}
	
	static function GetCurrentRemainAmount($RequestID, $computeArr=null, $forfeitInclude = true){
		
		$dt = array();
		if($computeArr == null)
			$computeArr = self::ComputePayments($RequestID, $dt);
		
		$CurrentRemain = 0;
		foreach($computeArr as $row)
		{
			if($row["ActionDate"] <= DateModules::Now())
			{
				$amount = $row["TotalRemainder"]*1;
				if($forfeitInclude) 
					$amount += isset($row["ForfeitAmount"]) ? $row["ForfeitAmount"]*1 : 0;
				$CurrentRemain = $amount < 0 ? 0 : $amount;
			}
			else
				break;
		}
		return $CurrentRemain;
	}
	
	static function GetTotalRemainAmount($RequestID, $computeArr=null){
		
		$dt = array();
		if($computeArr == null)
			$computeArr = self::ComputePayments($RequestID, $dt);
		
		if(count($computeArr) == 0)
			return 0;
		return $computeArr[count($computeArr)-1]["TotalRemainder"]*1 + 
				(isset($computeArr[ count($computeArr)-1 ]["ForfeitAmount"])*1 ?
					$computeArr[ count($computeArr)-1 ]["ForfeitAmount"] : 0);
		
	}
	
	static function GetTotalForfeitAmount($RequestID, $computeArr=null){
		
		$dt = array();
		if($computeArr == null)
			$computeArr = self::ComputePayments($RequestID, $dt);
		
		if(count($computeArr) == 0)
			return 0;
		$total = 0;
		foreach($computeArr as $row)
			$total += $row["CurForfeitAmount"]*1;
		
		return $total;		
	}
	
	/**
	 *
	 * @param int $RequestID
	 * @param array $computeArr
	 * @param array $PureArr
	 * @param gdate $ComputeDate
	 * @return  مانده اصل وام تا زمان دلخواه 
	 */
	static function GetPureAmount($RequestID, $computeArr=null, $PureArr = null, $ComputeDate = ""){

		if($PureArr == null)
			$PureArr = self::ComputePures($RequestID);
		if($computeArr == null)
			$computeArr = self::ComputePayments($RequestID, $dt);
		
		$partObj = LON_ReqParts::GetValidPartObj($RequestID);
		
		$ComputeDate = $ComputeDate == "" ? DateModules::Now() : $ComputeDate;		
		
		$TotalShouldPay = 0;
		$PureRemain = 0;
		for($i=0; $i < count($PureArr);$i++)
		{
			if($PureArr[$i]["InstallmentDate"] < $ComputeDate)
			{
				$TotalShouldPay += $PureArr[$i]["InstallmentAmount"]*1;
			}
			else
			{
				//-------- get wage until computedate -----------
				if($i == 0)
				{
					$PureRemain = $PureArr[0]["totalPure"];
					break;					
				}
				
				$fromDate = $i == 1 ? $partObj->PartDate : $PureArr[$i-1]["InstallmentDate"];
				
				$totalDays = DateModules::GDateMinusGDate($PureArr[$i]["InstallmentDate"], $fromDate);
				$days = DateModules::GDateMinusGDate($ComputeDate, $fromDate);
				
				$wage = $PureArr[$i]["wage"]*$days/$totalDays;
				
				$PureRemain = $PureArr[$i-1]["totalPure"]*1 + $wage;
				break;
			}
		}
		return array(
			"PureAmount" => $PureRemain == 0 ? $TotalShouldPay : $PureRemain,
			"TotalShouldPay" => $TotalShouldPay + $PureRemain
		);	
	}
	
	/**
	 *
	 * @param type $RequestID
	 * @param type $computeArr
	 * @param type $PureArr
	 * @param type $ComputeDate
	 * @return مانده در صورت تسویه وام 
	 */
	static function GetDefrayAmount($RequestID, $computeArr=null, $PureArr = null, $ComputeDate = ""){

		$dt = self::GetPureAmount($RequestID, $computeArr, $PureArr, $ComputeDate);
		
		$TotalShouldPay = $dt["TotalShouldPay"];
		
		//------------ sub pays --------------
		$dt = LON_BackPays::SelectAll(" RequestID=? 
			AND if(PayType=" . BACKPAY_PAYTYPE_CHEQUE . ",ChequeStatus=".INCOMECHEQUE_VOSUL.",1=1)"
			, array($RequestID));
		foreach($dt as $row)
			$TotalShouldPay -= $row["PayAmount"]*1;

		//-------- add costs ----------------
		$dt = LON_costs::Get(" AND RequestID=?", array($RequestID));
		$dt = $dt->fetchAll();
		foreach($dt as $row)
			$TotalShouldPay += $row["CostAmount"]*1;
		
		//-------- add forfeits ----------------
		foreach($computeArr as $row)
		{
			$TotalShouldPay += isset($row["CurForfeitAmount"]) ? $row["CurForfeitAmount"]*1 : 0;
			$TotalShouldPay += isset($row["LateWage"]) ? $row["LateWage"]*1 : 0;
			$TotalShouldPay += isset($row["LateForfeit"]) ? $row["LateForfeit"]*1 : 0;
		}
		
		return $TotalShouldPay<0 ? 0 : $TotalShouldPay;
	}
	
    /**
	 * تاریخ اولین قسطی که کامل پرداخت نشده است
	 * @param type $RequestID
	 * @param type $computeArr
	 * @return gdate 
	 */
	static function GetMinPayedInstallmentDate($RequestID, $computeArr=null){
		
		$dt = array();
		if($computeArr == null)
			$computeArr = self::ComputePayments($RequestID, $dt);
	
		foreach($computeArr as $row)
			if($row["ActionType"] == "installment")
			{
				if(count($row["pays"]) == 0)
					return $row["ActionDate"];
				
				$remain = $row["pays"][ count($row["pays"])-1 ]["remain"]*1;
				if($remain > 0)
					return $row["ActionDate"];
			}
		return null;
	}
	
	/**
	 * رکورد اولین قسطی که اصلا پرداخت نشده است
	 * @param type $RequestID
	 * @param type $computeArr
	 * @return gdate 
	 */
	static function GetNonPayedInstallmentRow($RequestID, $computeArr=null, $installments = array()){
		
		if($computeArr == null)
			$computeArr = self::ComputePayments($RequestID, $installments);
	
		foreach($installments as $row)
			if(isset($row["remainder"]) && $row["remainder"] == $row["InstallmentAmount"])
				return $row;
		return null;
	}
	
	/**
	 * طبقه تسهیلات را برمی گرداند
	 * @param int $RequestID
	 */
	static function GetRequestLevel($RequestID){
		
		$ComputeDate = "";
		$dt = LON_BackPays::GetRealPaid($RequestID);
		if(count($dt) == 0)
		{
			$dt = LON_installments::GetValidInstallments($RequestID);
			if(count($dt) == 0)
				$ComputeDate = DateModules::Now();
			else
				$ComputeDate = $dt[0]["InstallmentDate"];
		}
		else
		{
			$ComputeDate = $dt[ count($dt)-1 ]["PayDate"];
		}
		
		$diff = DateModules::GDateMinusGDate(DateModules::Now(), $ComputeDate);
		if($diff < 0)
			$diffInMonth = 0;
		else
			$diffInMonth = round($diff/30);
		
		$levels = PdoDataAccess::runquery("select * from ACC_CostCodeParamItems where ParamID=" . 
				ACC_COST_PARAM_LOAN_LEVEL);
		foreach($levels as $row)
		{
			if($diffInMonth >= $row["f1"]*1 && $diffInMonth <= $row["f2"]*1)
				return $row["ItemID"];
		}
	}
}

class LON_Computes extends PdoDataAccess{
	
	static function GetCurrentRemainAmount($RequestID, $computeArr=null){
		
		$dt = array();
		if($computeArr == null)
			$computeArr = self::ComputePayments($RequestID, $dt);
		
		$CurrentRemain = 0;
		foreach($computeArr as $row)
		{
			if($row["ActionDate"] <= DateModules::Now())
			{
				$amount = $row["TotalRemainder"]*1;
				$CurrentRemain = $amount < 0 ? 0 : $amount;
			}
			else
				break;
		}
		return $CurrentRemain;
	}
	
	static function GetTotalRemainAmount($RequestID, $computeArr=null){
		
		$dt = array();
		if($computeArr == null)
			$computeArr = self::ComputePayments($RequestID, $dt);
		
		if(count($computeArr) == 0)
			return 0;
		return $computeArr[count($computeArr)-1]["TotalRemainder"]*1;
		
	}
	
	static function GetDefrayAmount($RequestID, $computeArr=null, $PureArr = null, $ComputeDate = ""){

		$dt = LON_requests::GetPureAmount($RequestID, $computeArr, $PureArr, $ComputeDate);
		$EndingAmount = $dt["PureAmount"];
		$EndingInstallment = $dt["LastInstallmentID"];
		//----------------------
		for($i=count($computeArr)-1; $i != 0;$i--)
		{
			$row = $computeArr[$i];
			
			if($i == count($computeArr)-1 && $row["InstallmentID"] == 0)
			{
				$EndingAmount = $row["TotalRemainder"];
				break;
			}
			
			if($row["InstallmentID"] == $EndingInstallment || $EndingInstallment == 0)
			{
				$EndingAmount += $row["TotalRemainder"];
				//echo $row["TotalRemainder"] . "<br>";
				break;
			}
			
			if($row["ActionType"] == "pay")
			{
				$EndingAmount += $row["TotalRemainder"];
				break;
			}
		}
		return $EndingAmount;
	}
	
}

class LON_ReqParts extends PdoDataAccess{
	
	public $PartID;
	public $RequestID;
	public $PartDesc;
	public $PartDate;
	public $PartStartDate;
	public $PartAmount;
	public $InstallmentCount;
	public $IntervalType;
	public $PayInterval;
	public $DelayMonths;
	public $DelayDays;
	public $ForfeitPercent;
	public $DelayPercent;
	public $FundForfeitPercent;
	public $LatePercent;
	public $ForgivePercent;
	public $CustomerWage;
	public $FundWage;
	public $WageReturn;
	public $PayCompute;
	public $MaxFundWage;
	public $DelayReturn;
	public $AgentReturn;
	public $AgentDelayReturn;
	public $IsHistory;
	public $PayDuration;
	public $details;
	public $ComputeMode;
	public $BackPayCompute;
	public $FirstTotalWage;
	
	public $_BackPayComputeDesc;
	public $_param1;
	public $_param2;
	public $_param3;
	public $_param4;
	
	function __construct($PartID = "") {
		
		$this->DT_PartDate = DataMember::CreateDMA(DataMember::DT_DATE);
		$this->DT_PartStartDate = DataMember::CreateDMA(DataMember::DT_DATE);
		$this->DT_MaxFundWage = DataMember::CreateDMA(DataMember::DT_INT, 0);
		
		if($PartID != "")
			PdoDataAccess::FillObject ($this, "select p.*, 
					bf.InfoDesc _BackPayComputeDesc,
					bf.param1 _param1,
					bf.param2 _param2,
					bf.param3 _param3,
					bf.param4 _param4
				from LON_ReqParts p
				join BaseInfo bf on(TypeID=81 AND InfoID=BackPayCompute)
				where PartID=?", array($PartID));
	}
	
	static function SelectAll($where = "", $param = array()){
		
		return PdoDataAccess::runquery("
			select rp.*,r.StatusID,r.LoanPersonID,r.ReqPersonID, r.imp_VamCode,
				bf.InfoDesc BackPayComputeDesc,t.LocalNo,t.DocDate				
				
			from LON_ReqParts rp join LON_requests r using(RequestID)
			join BaseInfo bf on(TypeID=81 AND InfoID=BackPayCompute)
			left join (
				select SourceID2,LocalNo, DocDate from ACC_DocItems join ACC_docs using(DocID)
				where SourceType=".DOCTYPE_LOAN_DIFFERENCE."
				group by SourceID2
			)t on(SourceID2=rp.PartID)
			where " . $where, $param);
	}
	
	function AddPart($pdo = null){
		
		if (!parent::insert("LON_ReqParts", $this, $pdo)) {
			return false;
		}
		$this->PartID = parent::InsertID($pdo);
		
		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_add;
		$daObj->MainObjectID = $this->PartID;
		$daObj->TableName = "LON_ReqParts";
		$daObj->execute($pdo);
		return true;
	}
	
	function EditPart($pdo = null){
		
	 	if( parent::update("LON_ReqParts",$this," PartID=:l", array(":l" => $this->PartID), $pdo) === false )
	 		return false;

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_update;
		$daObj->MainObjectID = $this->PartID;
		$daObj->TableName = "LON_ReqParts";
		$daObj->execute($pdo);
	 	return true;
    }
	
	static function DeletePart($PartID, $pdo = null){
		
		if( parent::delete("LON_ReqParts"," PartID=?", array($PartID),$pdo) === false )
	 		return false;

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_delete;
		$daObj->MainObjectID = $PartID;
		$daObj->TableName = "LON_ReqParts";
		$daObj->execute($pdo);
	 	return true;
	}
	
	static function GetValidPartObj($RequestID){
		
		$dt = PdoDataAccess::runquery("select * from LON_ReqParts 
			where IsHistory='NO' AND RequestID=? order by PartID desc limit 1",array($RequestID));
		if(count($dt) == 0)
			return null;
		
		return new LON_ReqParts($dt[0]["PartID"]);
	}
	
	static function GetRejectParts(){

		return PdoDataAccess::runquery("
			select r.RequestID ,concat_ws(' ',p1.fname,p1.lname,p1.CompanyName) LoanPersonFullname
				from WFM_FlowRows fr
				join WFM_FlowSteps sp on(sp.FlowID=fr.FlowID AND fr.StepRowID=sp.StepRowID)
				join LON_ReqParts r on(PartID=ObjectID)
				join LON_requests using(RequestID)
				left join BSC_persons p1 on(p1.PersonID=LoanPersonID)
			where fr.FlowID=" . FLOWID_LOAN . " AND IsLastRow='YES' AND ActionType='REJECT' AND StepID=1");	
	}
}

class LON_installments extends PdoDataAccess{
	
	public $InstallmentID;
	public $RequestID;
	public $InstallmentDate;
	public $InstallmentAmount;
	public $wage;	
	public $IsDelayed;
	public $history;
			
	function __construct($InstallmentID = "") {
		
		$this->DT_InstallmentDate = DataMember::CreateDMA(DataMember::DT_DATE);
		$this->DT_PaidDate = DataMember::CreateDMA(DataMember::DT_DATE);
		
		if($InstallmentID != "")
			PdoDataAccess::FillObject ($this, "select * from LON_installments where InstallmentID=?", array($InstallmentID));
	}
	
	static function SelectAll($where = "", $param = array()){
		
		return PdoDataAccess::runquery("
			select i.*,r.*,p.* , group_concat(distinct LocalNo) docs
			from LON_installments i
			join LON_requests r using(RequestID)
			join LON_ReqParts p on(r.RequestID=p.RequestID AND p.IsHistory='NO')
			left join ACC_DocItems on(SourceType=" .DOCTYPE_INSTALLMENT_CHANGE. "
				AND SourceID1=i.RequestID AND SourceID2=i.InstallmentID)
			left join ACC_docs using(DocID)
			where " . $where . " group by i.InstallmentID", $param);
	}
	
	function AddInstallment($pdo = null){
		
	 	if(!parent::insert("LON_installments",$this, $pdo))
	 		return false;

		$this->InstallmentID = parent::InsertID($pdo);
		
		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_add;
		$daObj->MainObjectID = $this->InstallmentID;
		$daObj->TableName = "LON_installments";
		$daObj->execute($pdo);
	 	return true;
    }
	
	function EditInstallment($pdo = null){
		
	 	if( parent::update("LON_installments",$this," InstallmentID=:l", 
				array(":l" => $this->InstallmentID), $pdo) === false )
	 		return false;

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_update;
		$daObj->MainObjectID = $this->InstallmentID;
		$daObj->TableName = "LON_installments";
		$daObj->execute($pdo);
	 	return true;
    }
	
	static function GetValidInstallments($RequestID, $pdo = null){
		
		return PdoDataAccess::runquery("select * from 
			LON_installments where RequestID=? AND history='NO' AND IsDelayed='NO'
			order by InstallmentDate", 
			array($RequestID), $pdo);
		
	}
	
	static function GetLastInstallmentObj($RequestID){
		
		$obj = new LON_installments();
		PdoDataAccess::FillObject($obj, "
			select *
			from LON_installments
			where RequestID=?
			order by InstallmentDate desc limit 1", array($RequestID));
		return $obj;
	}
}

class LON_BackPays extends PdoDataAccess{
	
	public $BackPayID;
	public $RequestID;
	public $PayType;
	public $PayDate;
	public $PayAmount;
	public $PayRefNo;
	public $PayBillNo;
	public $details;
	public $IsGroup;
	public $IncomeChequeID;
	public $EqualizationID;
	
	function __construct($BackPayID = "") {
		
		$this->DT_PayDate = DataMember::CreateDMA(DataMember::DT_DATE);
		
		if($BackPayID != "")
			PdoDataAccess::FillObject ($this, "
				select *
				from LON_BackPays 
				where BackPayID=?", array($BackPayID));
	}
	
	static function SelectAll($where = "", $param = array()){
		
		$temp = preg_split("/order by/", $where);
		$where = $temp[0];
		$order = count($temp) > 1 ? " order by " . $temp[1] : "";
		
		return PdoDataAccess::runquery("
			select p.*,
				i.ChequeNo,
				i.ChequeStatus,
				t.InfoDesc ChequeStatusDesc,
				bi.InfoDesc PayTypeDesc, 				
				d.DocID,
				d.LocalNo,
				d.StatusID
			from LON_BackPays p
			left join BaseInfo bi on(bi.TypeID=6 AND bi.InfoID=p.PayType)
			left join ACC_IncomeCheques i using(IncomeChequeID)
			left join BaseInfo t on(t.TypeID=4 AND t.InfoID=ChequeStatus)
			
			left join ACC_ChequeHistory ch on(ch.IncomeChequeID = p.IncomeChequeID 
				AND ch.StatusID=" . INCOMECHEQUE_VOSUL . ")
			left join ACC_docs d on(ch.DocID=d.DocID)
			
			where " . $where . " group by BackPayID " . $order, $param);
	}
	
	function Add($pdo = null){
		
	 	if(!parent::insert("LON_BackPays",$this, $pdo))
	 		return false;
		
		$this->BackPayID = parent::InsertID($pdo);

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_add;
		$daObj->MainObjectID = $this->BackPayID;
		$daObj->TableName = "LON_BackPays";
		$daObj->execute($pdo);
	 	return true;
    }
	
	function Edit($pdo = null){
		
	 	if( parent::update("LON_BackPays",$this," BackPayID=:l", 
				array(":l" => $this->BackPayID), $pdo) === false )
	 		return false;

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_update;
		$daObj->MainObjectID = $this->BackPayID;
		$daObj->TableName = "LON_BackPays";
		$daObj->execute($pdo);
	 	return true;
    }
	
	static function DeletePay($BackPayID, $pdo = null){
		
		if( parent::delete("LON_BackPays"," BackPayID=?", array($BackPayID), $pdo) === false )
	 		return false;

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_delete;
		$daObj->MainObjectID = $BackPayID;
		$daObj->TableName = "LON_BackPays";
		$daObj->execute($pdo);
	 	return true;
	}
	
	static function GetAccDoc($BackPayID, $pdo = null){
		
		$obj = new LON_BackPays($BackPayID);
		
		$dt = PdoDataAccess::runquery("
			select DocID from ACC_DocItems where SourceType=" . DOCTYPE_INSTALLMENT_PAYMENT . " 
			AND SourceID1=? AND SourceID2=?" , array($obj->RequestID, $obj->BackPayID), $pdo);
		if(count($dt) == 0)
			return 0;
		return $dt[0][0];
	}
	
	static function GetRealPaid($RequestID){
		
		return LON_BackPays::SelectAll(" RequestID=? 
			AND if(PayType=" . BACKPAY_PAYTYPE_CHEQUE . ",ChequeStatus=".INCOMECHEQUE_VOSUL.",1=1)
			order by PayDate"
			, array($RequestID));
	}
}

class LON_payments extends OperationClass{
	
	const TableName = "LON_payments";
	const TableKey = "PayID";

	public $PayID;
	public $RequestID;
	public $PayType;
	public $PayDate;
	public $PayAmount; 
	
	function __construct($PayID = "") {
		
		$this->DT_PayDate = DataMember::CreateDMA(DataMember::DT_DATE);
		
		if($PayID != "")
			parent::FillObject ($this, "select *
				from LON_payments  where payID=?", array($PayID));
	}
	
	static function Get($where = '', $whereParams = array(), $order = "") {
		
		return parent::runquery_fetchMode("select p.*,d.DocID,d.LocalNo,d.StatusID 
			from LON_payments p
			left join ACC_DocItems di on(di.SourceType=" . DOCTYPE_LOAN_PAYMENT . " 
				AND di.SourceID1=p.RequestID AND di.SourceID3=p.PayID) 
			left join ACC_docs d on(di.DocID=d.DocID)
			where 1=1 " . $where .  
			" group by p.PayID " . $order, $whereParams);
	}
	
	function CheckPartAmount(){
		
		$dt = parent::runquery("select ifnull(sum(PayAmount),0) from LON_payments 
			where RequestID=? AND PayID<>?", array($this->RequestID, $this->PayID));
		
		$PartObj = LON_ReqParts::GetValidPartObj($this->RequestID);
		
		if($dt[0][0]*1 + $this->PayAmount*1 > $PartObj->PartAmount*1)
		{
			ExceptionHandler::PushException("مبالغ وارد شده از سقف مبلغ وام تجاوز می کند");
			return false;
		}
		
		return true;
	}
	
	function Add($pdo = null) {
		
		if(!$this->CheckPartAmount())
			return false;
		
		return parent::Add($pdo);
	}
	
	function Edit($pdo = null) {
		
		if(!$this->CheckPartAmount())
			return false;
		
		return parent::Edit($pdo);
	}
	
	static function GetDocID($PayID){
		
		$dt = parent::runquery("select d.DocID
			from LON_payments p
			left join ACC_DocItems di on(di.SourceType=" . DOCTYPE_LOAN_PAYMENT . " 
				AND SourceID1=p.RequestID AND SourceID3=p.PayID) 
			left join ACC_docs d on(di.DocID=d.DocID)
			where p.PayID=? ", array($PayID));
		return count($dt) > 0 ? $dt[0][0] : 0;
	}
}

class LON_messages extends OperationClass {

	const TableName = "LON_messages";
	const TableKey = "MessageID";
	
	public $MessageID;
	public $RequestID;
	public $RegPersonID;
	public $CreateDate;
	public $details;
	public $MsgStatus;
	public $DoneDate;
	public $DoneDesc;
	
	static function Get($where = '', $whereParams = array(), $order = "order by CreateDate desc") {
		
		return PdoDataAccess::runquery_fetchMode("
		select	m.* , r.BorrowerDesc,
				concat_ws(' ',p.fname,p.lname,p.CompanyName) RegPersonName,
				concat_ws(' ',p2.fname,p2.lname,p2.CompanyName) LoanFullname
				
		from LON_messages m
		join BSC_persons p on(RegPersonID = PersonID)
		join LON_requests r using(RequestID)
		left join BSC_persons p2 on(p2.PersonID=r.LoanPersonID)
		
		where 1=1 " . $where . " " . $order, $whereParams);	
	}
}

class LON_events extends OperationClass {

    const TableName = "LON_events";
    const TableKey = "EventID";

    public $EventID;
	public $RequestID;
	public $RegPersonID;
    public $EventTitle;
    public $EventDate;
	public $LetterID;
	public $FollowUpDate;
	public $FollowUpDesc;
	public $FollowUpPersonID;
	  
    function __construct($id = ""){
        
		$this->DT_EventDate = DataMember::CreateDMA(DataMember::DT_DATE);
		$this->DT_FollowUpDate = DataMember::CreateDMA(DataMember::DT_DATE);
		
        parent::__construct($id);
    }

	static function Get($where = '', $whereParams = array(), $pdo = null) {
		
		return PdoDataAccess::runquery_fetchMode("
			select e.*, concat_ws(' ',p1.CompanyName,p1.fname,p1.lname) RegFullname, 
				concat_ws(' ',p2.CompanyName,p2.fname,p2.lname) FollowUpFullname
			from LON_events e 
				left join BSC_persons p1 on(p1.PersonID=RegPersonID)
				left join BSC_persons p2 on(p2.PersonID=FollowUpPersonID)
			where 1=1 " . $where, $whereParams, $pdo);
	}
	
}

class LON_costs extends OperationClass{
	
	const TableName = "LON_costs";
	const TableKey = "CostID";
	
	public $CostID;
	public $RequestID;
	public $CostDesc;
	public $CostAmount;
	public $IsPartDiff;
	public $PartID;
	public $CostDate;
	
	function __construct($id = '') {
		
		$this->DT_CostDate = DataMember::CreateDMA(DataMember::DT_DATE);
		
		parent::__construct($id);
	}
	
	static function Get($where = '', $whereParams = array(), $pdo = null) {
		
		return PdoDataAccess::runquery_fetchMode("
			select c.*,d.LocalNo from LON_costs c
			left join ACC_DocItems di on(c.CostID=di.SourceID2 AND di.SourceType=17)
			left join ACC_docs d using(DocID)
			where 1=1 " . $where . " group by CostID", $whereParams, $pdo);
	}
	
	function GetAccDoc(){
		
		$dt = PdoDataAccess::runquery("select d.* 
			from ACC_DocItems di join ACC_docs d using(DocID) 
			where SourceID1=? AND SourceID2=? AND SourceType=17
			group by DocID", array($this->RequestID, $this->CostID));
		
		return count($dt) > 0 ? $dt[0] : false;
	}

}

class LON_guarantors extends OperationClass{
	
	const TableName = "LON_guarantors";
	const TableKey = "GuarantorID";
	
	public $GuarantorID;
	public $RequestID;
	public $sex;
	public $fullname;
	public $NationalCode;
	public $father;
	public $ShNo;
	public $ShCity;
	public $BirthDate;
	public $address;
	public $phone;
	public $mobile;
	public $PersonType;
	
	function __construct($id = '') {
		
		$this->DT_BirthDate = DataMember::CreateDMA(DataMember::DT_DATE);
		
		parent::__construct($id);
	}
}
?>
