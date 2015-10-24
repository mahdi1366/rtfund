<?php
//---------------------------
// programmer:	Jafarkhani
// create Date: 94.06
//---------------------------

class LON_loans extends PdoDataAccess
{
	public $LoanID;
	public $GroupID;
	public $LoanDesc;
	public $MaxAmount;
	public $PayCount;
	public $IntervalType;
	public $PayInterval;
	public $DelayMonth;
	public $ForfeitPercent;
	public $CustomerWage;
	public $FundWage;
			
	function __construct($LoanID = "") {
		
		if($LoanID != "")
			PdoDataAccess::FillObject ($this, "select * from LON_loans where LoanID=?", array($LoanID));
	}
	
	static function SelectAll($where = "", $param = array()){
		
		return PdoDataAccess::runquery_fetchMode("select l.*,InfoDesc GroupDesc from LON_loans l
			join BaseInfo bf on(bf.TypeID=1 AND bf.InfoID=l.GroupID)
			where " . $where, $param);
	}
	
	function AddLoan()
	{
	 	if(!parent::insert("LON_loans",$this))
			return false;
		$this->LoanID = parent::InsertID();
		
		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_add;
		$daObj->MainObjectID = $this->LoanID;
		$daObj->TableName = "LON_loans";
		$daObj->execute();
		return true;
	}
	
	function EditLoan()
	{
	 	if( parent::update("LON_loans",$this," LoanID=:l", array(":l" => $this->LoanID)) === false )
	 		return false;

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_update;
		$daObj->MainObjectID = $this->LoanID;
		$daObj->TableName = "LON_loans";
		$daObj->execute();
	 	return true;
    }
	
	static function DeleteLoan($LoanID){
		
		if( parent::delete("LON_loans"," LoanID=?", array($LoanID)) === false )
	 		return false;

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_delete;
		$daObj->MainObjectID = $LoanID;
		$daObj->TableName = "LON_loans";
		$daObj->execute();
	 	return true;
	}
}

?>
