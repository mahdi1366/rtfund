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
	
	function __construct($EventID, $BranchID) {
	
		$this->EventID = $EventID;
		$this->BranchID = $BranchID;
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
			select * from COM_EventRows er join CON_events e using(EventID) 
			where er.EventID=? AND er.IsActive='YES'
			Order by RowID", 
			array($this->EventID), $this->pdo);

		if(count($eventRows) == 0)
		{
			ExceptionHandler::PushException("رویداد فاقد ردیف می باشد");
			return false;
		}

		if($this->DocObj)
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
		foreach ($eventRows as $event)
		{
			//-- insert DocHeaders if at least one row would be added --
			if(empty($this->DocObj->DocID))
			{
				if(!$this->DocObj->Add($pdo))
				{
					ExceptionHandler::PushException("خطا در ایجاد سند");
					return false;
				}
			}
			//----------------------------------------------------------

			$obj = new ACC_DocItems();
			$obj->DocID = $this->DocObj->DocID;
			$result = self::FillItemObject($event, $obj);	
			if(!$result)
				continue;

			if(!$obj->Add($this->pdo))
			{
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
	
	private function FillItemObject($event, &$obj, $amountRow = ""){
		
		/*@var $obj ACC_DocItems */
		
		//------------------ set amounts ------------------------
		$fn = $this->EventFunction;
		$amount = $fn($event["ComputeItemID"], $this->EventFunctionParams)*1;
		if($amount == 0)
			return false;
		
		$obj->DebtorAmount = $event["CostType"] == "DEBTOR" ? $amount : 0;
		$obj->CreditorAmount = $event["CostType"] == "CREDITOR" ? $amount : 0;
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
		if($event["tafsili"]*1 > 0)
		{
			$result = EventComputeItems::GetTafsilis($event["tafsili"]*1, $this->EventFunctionParams);
			$obj->TafsiliType = $result[0];
			$obj->TafsiliID = $result[1];
		}
		else
		{
			$obj->TafsiliType = $event["TafsiliType"];
			if(!empty($this->tafsilis[ $obj->TafsiliType ]))
				$obj->TafsiliID = $this->tafsilis[ $obj->TafsiliType ];
		}
		
		if($event["tafsili2"]*1 > 0)
		{
			$result = EventComputeItems::GetTafsilis($event["tafsili2"]*1, $this->EventFunctionParams);
			$obj->TafsiliType2 = $result[0];
			$obj->TafsiliID2 = $result[1];
		}
		else
		{
			$obj->TafsiliType2 = $event["TafsiliType2"];
			if(!empty($this->tafsilis[ $obj->TafsiliType2 ]))
				$obj->TafsiliID2 = $this->tafsilis[ $obj->TafsiliType2 ];
		}
		
		if($event["tafsili3"]*1 > 0)
		{
			$result = EventComputeItems::GetTafsilis($event["tafsili3"]*1, $this->EventFunctionParams);
			$obj->TafsiliType3 = $result[0];
			$obj->TafsiliID3 = $result[1];
		}
		else
		{
			$obj->TafsiliType3 = $event["TafsiliType3"];
			if(!empty($this->tafsilis[ $obj->TafsiliType3 ]))
				$obj->TafsiliID3 = $this->tafsilis[ $obj->TafsiliType3 ];
		}
		
		//------------------- set SourceIDs  ---------------------
		if(is_array($this->Sources))
			for($i=0; $i < count($this->Sources); $i++)
				$obj->{ "SourceID" . ($i==0 ? "" : $i+1) } = $this->Sources[$i];
		else
			$obj->SourceID = $this->Sources;
		
		return true;
	}

}
