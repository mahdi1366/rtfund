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
	public $ReqPersonID;
	public $ReqDate;
	public $ReqAmount;
	public $StatusID;
	public $ReqDetails;
	public $BorrowerDesc;
	public $BorrowerID;
	public $LoanPersonID;
	public $assurance;
	public $AgentGuarantee;
			
	function __construct($RequestID = "") {
		
		if($RequestID != "")
			PdoDataAccess::FillObject ($this, "select * from LON_requests where RequestID=?", array($RequestID));
	}
	
	static function SelectAll($where = "", $param = array()){
		
		return PdoDataAccess::runquery("
			select r.*,
				if(p1.IsReal='YES',concat(p1.fname, ' ', p1.lname),p1.CompanyName) ReqFullname,
				if(p2.IsReal='YES',concat(p2.fname, ' ', p2.lname),p2.CompanyName) LoanFullname,
				l.LoanDesc,
				l.MaxAmount, 
				bi.InfoDesc StatusDesc,
				BranchName
			from LON_requests r
			join BSC_branches using(BranchID)
			left join LON_loans l using(LoanID)
			join BaseInfo bi on(bi.TypeID=5 AND bi.InfoID=StatusID)
			join BSC_persons p1 on(p1.PersonID=r.ReqPersonID)
			left join BSC_persons p2 on(p2.PersonID=r.LoanPersonID)
			where " . $where, $param);
	}
	
	function AddRequest($pdo = null)
	{
		$this->ReqDate = PDONOW;
		
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
		
		if( parent::delete("LON_reqParts"," RequestID=?", array($RequestID)) === false )
	 		return false;
		
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

class LON_ReqParts extends PdoDataAccess
{
	public $PartID;
	public $RequestID;
	public $PartDesc;
	public $PayDate;
	public $PartAmount;
	public $PayCount;
	public $IntervalType;
	public $PayInterval;
	public $DelayMonths;
	public $ForfeitPercent;
	public $CustomerFee;
	public $FundFee;
	public $AgentFee;

			
	function __construct($PartID = "") {
		
		$this->DT_PayDate = DataMember::CreateDMA(DataMember::DT_DATE);
		
		if($PartID != "")
			PdoDataAccess::FillObject ($this, "select * from LON_ReqParts where PartID=?", array($PartID));
	}
	
	static function SelectAll($where = "", $param = array()){
		
		return PdoDataAccess::runquery("
			select r.*
			from LON_ReqParts r
			where " . $where, $param);
	}
	
	function AddPart($pdo = null)
	{
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
	
	function EditPart()
	{
	 	if( parent::update("LON_ReqParts",$this," PartID=:l", array(":l" => $this->PartID)) === false )
	 		return false;

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_update;
		$daObj->MainObjectID = $this->PartID;
		$daObj->TableName = "LON_ReqParts";
		$daObj->execute();
	 	return true;
    }
	
	static function DeletePart($PartID){
		
		if( parent::delete("LON_ReqParts"," PartID=?", array($PartID)) === false )
	 		return false;

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_delete;
		$daObj->MainObjectID = $PartID;
		$daObj->TableName = "LON_ReqParts";
		$daObj->execute();
	 	return true;
	}
}

class LON_PartPayments extends PdoDataAccess
{
	public $PayID;
	public $PartID;
	public $PayDate;
	public $PayAmount;
	public $FeeAmount;
	public $FeePercent;
	public $PaidDate;
	public $PaidAmount;
	public $StatusID;
			
	function __construct($PayID = "") {
		
		$this->DT_PayDate = DataMember::CreateDMA(DataMember::DT_DATE);
		$this->DT_PaidDate = DataMember::CreateDMA(DataMember::DT_DATE);
		
		if($PayID != "")
			PdoDataAccess::FillObject ($this, "select * from LON_PartPayments where PayID=?", array($PayID));
	}
	
	static function SelectAll($where = "", $param = array()){
		
		return PdoDataAccess::runquery("
			select p.*
			from LON_PartPayments p
			where " . $where, $param);
	}
	
	function AddPart($pdo = null)
	{
		if (!parent::insert("LON_PartPayments", $this, $pdo)) {
			return false;
		}
		$this->PayID = parent::InsertID($pdo);
		
		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_add;
		$daObj->MainObjectID = $this->PayID;
		$daObj->TableName = "LON_PartPayments";
		$daObj->execute($pdo);
		return true;
	}
	
	function EditPart()
	{
	 	if( parent::update("LON_PartPayments",$this," PayID=:l", array(":l" => $this->PayID)) === false )
	 		return false;

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_update;
		$daObj->MainObjectID = $this->PayID;
		$daObj->TableName = "LON_PartPayments";
		$daObj->execute();
	 	return true;
    }
	
	static function DeletePart($PayID){
		
		if( parent::delete("LON_PartPayments"," PayID=?", array($PayID)) === false )
	 		return false;

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_delete;
		$daObj->MainObjectID = $PayID;
		$daObj->TableName = "LON_PartPayments";
		$daObj->execute();
	 	return true;
	}
}

?>
