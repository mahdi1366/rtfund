<?php
//---------------------------
// programmer:	Jafarkhani
// create Date: 94.06
//---------------------------

class LON_requests extends PdoDataAccess
{
	public $RequestID;
	public $BranchID;
	public $LoanID;
	public $PersonID;
	public $ReqDate;
	public $ReqAmount;
	public $OkAmount;
	public $StatusID;
	public $ReqDetails;
	
	public $PartCount;
	public $PartInterval;
	public $DelayCount;
	public $InsureAmount;
	public $FirstPartAmount;
	public $ForfeitPercent;
	public $FeePercent;
	public $FeeAmount;
	public $ProfitPercent;
			
	function __construct($RequestID = "") {
		
		if($RequestID != "")
			PdoDataAccess::FillObject ($this, "select * from LON_requests where RequestID=?", array($RequestID));
	}
	
	static function SelectAll($where = "", $param = array()){
		
		return PdoDataAccess::runquery("
			select r.*,concat(fname, ' ', lname) fullname,
				l.LoanDesc,l.MaxAmount, 
				bi.InfoDesc StatusDesc,
				BranchName
			from LON_requests r
			join BSC_branches using(BranchID)
			join LON_loans l using(LoanID)
			join BaseInfo bi on(bi.TypeID=5 AND bi.InfoID=StatusID)
			join BSC_persons using(PersonID)
			where " . $where, $param);
	}
	
	function AddRequest()
	{
		$this->ReqDate = PDONOW;
		
	 	if(!parent::insert("LON_requests",$this))
			return false;
		$this->RequestID = parent::InsertID();
		
		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_add;
		$daObj->MainObjectID = $this->RequestID;
		$daObj->TableName = "LON_requests";
		$daObj->execute();
		return true;
	}
	
	function EditRequest()
	{
	 	if( parent::update("LON_requests",$this," RequestID=:l", array(":l" => $this->RequestID)) === false )
	 		return false;

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_update;
		$daObj->MainObjectID = $this->RequestID;
		$daObj->TableName = "LON_requests";
		$daObj->execute();
	 	return true;
    }
	
	static function DeleteRequest($RequestID){
		
		if( parent::delete("LON_requests"," RequestID=?", array($RequestID)) === false )
	 		return false;

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_delete;
		$daObj->MainObjectID = $RequestID;
		$daObj->TableName = "LON_requests";
		$daObj->execute();
	 	return true;
	}
}

class LON_RequestParts extends PdoDataAccess
{
	public $PartID;
	public $PartDate;
	public $PartAmount;
	public $StatusID;
			
	function __construct($PartID = "") {
		
		if($PartID != "")
			PdoDataAccess::FillObject ($this, "select * from LON_RequestParts where PartID=?", array($PartID));
	}
	
	static function SelectAll($where = "", $param = array()){
		
		return PdoDataAccess::runquery("
			select r.*, bi.InfoDesc StatusDesc
			from LON_RequestParts r
			join BaseInfo bi on(bi.TypeID=6 AND bi.InfoID=StatusID)
			where " . $where, $param);
	}
	
	function AddPart()
	{
		$this->ReqDate = PDONOW;
		
		if (!parent::insert("LON_RequestParts", $this)) {
			return false;
		}
		$this->PartID = parent::InsertID();
		
		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_add;
		$daObj->MainObjectID = $this->PartID;
		$daObj->TableName = "LON_RequestParts";
		$daObj->execute();
		return true;
	}
	
	function EditPart()
	{
	 	if( parent::update("LON_RequestParts",$this," PartID=:l", array(":l" => $this->PartID)) === false )
	 		return false;

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_update;
		$daObj->MainObjectID = $this->PartID;
		$daObj->TableName = "LON_RequestParts";
		$daObj->execute();
	 	return true;
    }
	
	static function DeletePart($PartID){
		
		if( parent::delete("LON_RequestParts"," PartID=?", array($PartID)) === false )
	 		return false;

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_delete;
		$daObj->MainObjectID = $PartID;
		$daObj->TableName = "LON_RequestParts";
		$daObj->execute();
	 	return true;
	}
}

?>
