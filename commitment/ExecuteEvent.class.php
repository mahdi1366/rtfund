<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 97.09
//-----------------------------

require_once DOCUMENT_ROOT . '/accounting/docs/doc.class.php';
require_once "ComputeItems.class.php";

class ExecuteEvent {
	
	private $pdo;
	public $EventID;
	public $BranchID;
	
	public $DocObj;
	
	public $EventFunction;
	public $EventFunctionParams;
	public $Sources;
	public $tafsilis = array();
	public $ComputedItems = array();
	
	function __construct($EventID, $BranchID = "") {
	
		$this->EventID = $EventID;
		$this->BranchID = $BranchID == "" ? BRANCH_BASE : $BranchID;
		
		switch($this->EventID)
		{
			case EVENT_LOAN_ALLOCATE:
				$this->EventFunction = "EventComputeItems::LoanAllocate";
				break;	
			case EVENT_LOANPAYMENT_agentSource:
			case EVENT_LOANPAYMENT_innerSource:
			case EVENT_LOANCONTRACT_innerSource:
			case EVENT_LOANCONTRACT_agentSource_committal:
			case EVENT_LOANCONTRACT_agentSource_non_committal:
				$this->EventFunction = "EventComputeItems::PayLoan";
				break;
			
			case EVENT_LOANBACKPAY_innerSource:
			case EVENT_LOANBACKPAY_agentSource_committal:
			case EVENT_LOANBACKPAY_agentSource_non_committal:
				$this->EventFunction = "EventComputeItems::LoanBackPay";
				break;
			
			case EVENT_LOANDAILY_innerSource:
			case EVENT_LOANDAILY_agentSource_committal:
			case EVENT_LOANDAILY_agentSource_non_committal:
				$this->EventFunction = "EventComputeItems::LoanDaily";
				break;			
		}
	}
	
	function RegisterEventDoc($pdo = null){
		
		if($pdo == null)
		{
			$this->pdo = parent::getPdoObject();
			$this->pdo->beginTransaction();
		}
		else
			$this->pdo = &$pdo;

		$this->ComputedItems = array();
			
		$eventRows = PdoDataAccess::runquery("
			select * from COM_EventRows er 
				join COM_events e using(EventID) 
				join  ACC_CostCodes cc using(CostID)
			where er.EventID=? AND er.IsActive='YES'
			order by CostType,CostCode", 
			array($this->EventID), $this->pdo);

		if(count($eventRows) == 0)
		{
			ExceptionHandler::PushException("رویداد فاقد ردیف می باشد");
			return false;
		}

		if(!$this->DocObj)
		{
			$this->DocObj = new ACC_docs();
			$this->DocObj->RegDate = PDONOW;
			$this->DocObj->regPersonID = $_SESSION['USER']["PersonID"];
			$this->DocObj->DocDate = PDONOW;
			$this->DocObj->CycleID = $_SESSION["accounting"]["CycleID"];
			$this->DocObj->BranchID = $this->BranchID;
			$this->DocObj->DocType = DOCTYPE_EXECUTE_EVENT;
			$this->DocObj->EventID = $this->EventID;
			$this->DocObj->description = "اجرای رویداد[ " . $this->EventID . " ] " . $eventRows[0]["EventTitle"];
		}

		//----------------------- add doc items -------------------
		foreach ($eventRows as $eventRow)
		{
			if(!$this->AddDocItem($eventRow))
			{
				if($pdo == null)
					$this->pdo->rollBack();
				return false;
			}
		}
		//....................................................

		if($pdo == null)
			$this->pdo->commit();
		return true;
	}
	
	private function AddDocItem($eventRow, $amount = null){
		
		$obj = new ACC_DocItems();
		$obj->DocID = $this->DocObj->DocID;
		$obj->CostID = $eventRow["CostID"];
		$obj->locked = "YES";
		//------------------ set amounts ------------------------
		if($amount == null) 
		{
			if($eventRow["ComputeItemID"]*1 > 0)
			{
				if(isset($this->ComputedItems[ $eventRow["ComputeItemID"] ]))
					$amount = $this->ComputedItems[ $eventRow["ComputeItemID"] ];
				else
				{
					$amount = call_user_func($this->EventFunction, $eventRow["ComputeItemID"], $this->Sources);
					$this->ComputedItems[ $eventRow["ComputeItemID"] ] = $amount;
				}
				if(is_array($amount))
				{
					if(isset($amount["amount"]))
					{
						PdoDataAccess::FillObjectByArray($obj, $amount);
						$amount = $amount["amount"];
					}
					else
					{
						foreach($amount as $amountRow)
						{
							if(!$this->AddDocItem($eventRow, $amountRow))
								return false;
						}
						return true;
					}
				}
			}
			else
			{
				if($eventRow["CostType"] == "DEBTOR")
					$amount = isset($_POST["DebtorAmount_" . $eventRow["RowID"]]) ? 
						$_POST["DebtorAmount_" . $eventRow["RowID"]] : 0;
				else
					$amount = isset($_POST["CreditorAmount_" . $eventRow["RowID"]]) ? 
						$_POST["CreditorAmount_" . $eventRow["RowID"]] : 0;
				$amount = preg_replace("/,/", "", $amount);
			}
		}
		else 
		{
			if(is_array($amount) && isset($amount["amount"]))
			{
				PdoDataAccess::FillObjectByArray($obj, $amount);
				$amount = $amount["amount"];
				
			}
		}
		
		if($amount == 0)
			return true;
		
		$obj->DebtorAmount = $eventRow["CostType"] == "DEBTOR" ? $amount : 0;
		$obj->CreditorAmount = $eventRow["CostType"] == "CREDITOR" ? $amount : 0;
		if($obj->DebtorAmount < 0)
		{
			$obj->CreditorAmount = -1*$obj->DebtorAmount;
			$obj->DebtorAmount = 0;
		}
		if($obj->CreditorAmount < 0)
		{
			$obj->DebtorAmount = -1*$obj->CreditorAmount;
			$obj->CreditorAmount = 0;
		}	
		
		//---------------- set tafsilis --------------------
		$obj->TafsiliType = $eventRow["TafsiliType1"];
		$obj->TafsiliType2 = $eventRow["TafsiliType2"];
		$obj->TafsiliType3 = $eventRow["TafsiliType3"];
		$result = EventComputeItems::SetSpecialTafsilis($this->EventID, $eventRow, $this->Sources);
		$obj->TafsiliID = $result[0]["TafsiliID"];
		$obj->TafsiliID2 = $result[1]["TafsiliID"];
		$obj->TafsiliID3 = $result[2]["TafsiliID"];
		//------------------- set SourceIDs  ---------------------
		if(is_array($this->Sources))
			for($i=0; $i < count($this->Sources); $i++)
				$obj->{ "SourceID" . ($i+1) } = (int)$this->Sources[$i];
		else
			$obj->SourceID = $this->Sources;
		//------------------- set params  ---------------------
		EventComputeItems::SetParams($this->EventID, $eventRow, $this->Sources, $obj);
		
		//-- insert DocHeaders if at least one row would be added --
		if(empty($this->DocObj->DocID))
		{
			if(!$this->DocObj->Add($this->pdo))
			{
				ExceptionHandler::PushException("خطا در ایجاد سند");
				return false;
			}
			$obj->DocID = $this->DocObj->DocID;
		}
		//-------------------------------------------------------------
		
		if(!$obj->Add($this->pdo))
		{
			ExceptionHandler::PushException("خطا در صدور ردیف های سند تعهدی");
			return false;
		}
		return true;
	}

	static function GetRegisteredDoc($EventID, $SourceIDs){
	
		$params = array(":e" => $EventID);
		$query = "select LocalNo 
				from ACC_DocItems join ACC_docs using(DocID)
				where EventID=:e ";

		if(!is_array($SourceIDs))
			$SourceIDs = array($SourceIDs);

		$index = 0;
		foreach($SourceIDs as $sourceID)
		{
			$query .= " AND SourceID" . ($index+1) . "=:s". $index;
			$params[":s" . $index] = $sourceID;
			$index++;
		}

		$dt = PdoDataAccess::runquery($query, $params);
		return count($dt) > 0 ? $dt[0]["LocalNo"] : false;
	}
}
