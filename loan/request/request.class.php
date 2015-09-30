<?php
//---------------------------
// programmer:	Jafarkhani
// create Date: 94.06
//---------------------------

class LON_requests extends PdoDataAccess
{
	public $RequestID;
	public $LoanID;
	public $PersonID;
	public $ReqDate;
	public $ReqAmount;
	public $OkAmount;
	public $StatusID;
	public $ReqDetails;
			
	function __construct($RequestID = "") {
		
		if($RequestID != "")
			PdoDataAccess::FillObject ($this, "select * from LON_requests where RequestID=?", array($RequestID));
	}
	
	static function SelectAll($where = "", $param = array()){
		
		return PdoDataAccess::runquery("
			select r.*,concat(fname, ' ', lname) fullname,l.*, bi.InfoDesc StatusDesc
			from LON_requests r
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

?>
