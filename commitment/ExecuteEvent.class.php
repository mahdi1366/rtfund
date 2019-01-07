<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 97.09
//-----------------------------

require_once getenv("DOCUMENT_ROOT") . '/accounting/docs/doc.class.php';

class ExecuteEvent {
	
	private $pdo;
	private $EventID;
	private $BranchID;
	
	public $DocObj;
	
	public $EventFunction;
	public $EventFunctionParams;
	public $Sources;
	public $tafsilis = array();
	
	function __construct($EventID, $BranchID = "") {
	
		$this->EventID = $EventID;
		$this->BranchID = $BranchID == "" ? BRANCH_BASE : $BranchID;
		
		switch($this->EventID)
		{
			case EVENT_LOAN_PAYMENT:
				$this->EventFunction = "EventComputeItems::PayLoan";
		}
	}
	
	function SetSources($sourceIDs){
		
		switch($this->EventID)
		{
			case EVENT_LOAN_PAYMENT:
				$ReqObj = new LON_requests((int)$sourceIDs[0]);
				$PartObj = new LON_ReqParts((int)$sourceIDs[1]);
				$PayObj = new LON_payments((int)$sourceIDs[2]);
				$this->EventFunctionParams = array($ReqObj, $PartObj, $PayObj);
				$this->Sources = $sourceIDs;
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
			//-- insert DocHeaders if at least one row would be added --
			if(empty($this->DocObj->DocID))
			{
				if(!$this->DocObj->Add($pdo))
				{
					if($pdo == null)
						$this->pdo->rollBack();
					ExceptionHandler::PushException("خطا در ایجاد سند");
					return false;
				}
			}
			//----------------------------------------------------------

			$obj = new ACC_DocItems();
			$obj->DocID = $this->DocObj->DocID;
			$obj->CostID = $eventRow["CostID"];
			$result = self::FillItemObject($eventRow, $obj);	
			if(!$result)
				continue;

			if(!$obj->Add($this->pdo))
			{
				if($pdo == null)
					$this->pdo->rollBack();
				ExceptionHandler::PushException("خطا در صدور ردیف های سند تعهدی");
				return false;
			}
		}
		//....................................................

		if($pdo == null)
			$this->pdo->commit();
		return true;
	}
	
	private function FillItemObject($eventRow, &$obj){
		
		/*@var $obj ACC_DocItems */
		
		//------------------ set amounts ------------------------
		if($eventRow["ComputeItemID"]*1 > 0)
		{
			$amount = call_user_func($this->EventFunction,
					$eventRow["ComputeItemID"], $this->EventFunctionParams);
			$obj->locked = "YES";
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
		if($amount == 0)
			return false;
		
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
		if($eventRow["Tafsili"]*1 > 0)
		{
			$result = EventComputeItems::GetTafsilis($eventRow["Tafsili"]*1, $this->EventFunctionParams);
			$obj->TafsiliType = $result[0];
			$obj->TafsiliID = $result[1];
		}
		else
		{
			$obj->TafsiliType = $eventRow["TafsiliType"];
			if(!empty($_POST["TafsiliID_" . $eventRow["RowID"]]))
				$obj->TafsiliID = $_POST["TafsiliID_" . $eventRow["RowID"]];
		}
		
		if($eventRow["Tafsili2"]*1 > 0)
		{
			$result = EventComputeItems::GetTafsilis($eventRow["Tafsili2"]*1, $this->EventFunctionParams);
			$obj->TafsiliType2 = $result[0];
			$obj->TafsiliID2 = $result[1];
		}
		else
		{
			$obj->TafsiliType2 = $eventRow["TafsiliType2"];
			if(!empty($_POST["TafsiliID2_" . $eventRow["RowID"]]))
				$obj->TafsiliID = $_POST["TafsiliID2_" . $eventRow["RowID"]];
		}
		
		if($eventRow["Tafsili3"]*1 > 0)
		{
			$result = EventComputeItems::GetTafsilis($eventRow["Tafsili3"]*1, $this->EventFunctionParams);
			$obj->TafsiliType3 = $result[0];
			$obj->TafsiliID3 = $result[1];
		}
		else
		{
			$obj->TafsiliType3 = $eventRow["TafsiliType3"];
			if(!empty($_POST["TafsiliID3_" . $eventRow["RowID"]]))
				$obj->TafsiliID = $_POST["TafsiliID3_" . $eventRow["RowID"]];
		}
		
		//------------------- set SourceIDs  ---------------------
		if(is_array($this->Sources))
			for($i=0; $i < count($this->Sources); $i++)
				$obj->{ "SourceID" . ($i==0 ? "" : $i+1) } = $this->Sources[$i];
		else
			$obj->SourceID = $this->Sources;
		
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
			$query .= " AND SourceID" . ($index==0 ? "" : $index+1) . "=:s". $index;
			$params[":s" . $index] = $sourceID;
			$index++;
		}

		$dt = PdoDataAccess::runquery($query, $params);
		return count($dt) > 0 ? $dt[0]["LocalNo"] : false;
	}
}
