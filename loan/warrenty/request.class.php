<?php
//---------------------------
// programmer:	Jafarkhani
// create Date: 95.06
//---------------------------

require_once getenv("DOCUMENT_ROOT") . '/office/dms/dms.class.php';

class WAR_requests extends OperationClass
{
	const TableName = "WAR_requests";
	const TableKey = "RequestID";
	
	public $RequestID;
	public $TypeID;
	public $PersonID;
	public $organization;
	public $ReqDate;
	public $amount;
	public $StatusID;
	
	public $_fullname;
	
	function _construct($RequestID = "") {
		
		$this->DT_ReqDate = DataMember::CreateDMA(DataMember::DT_DATE);
		
		if($RequestID != "")
			PdoDataAccess::FillObject ($this, "
				select r.* , concat_ws(' ',fname,lname,CompanyName) _fullname
					from WAR_requests r 
					left join BSC_persons using(PersonID)
				where RequestID=?", array($RequestID));
	}
	
	static function SelectAll($where = "", $param = array()){
		
		return PdoDataAccess::runquery_fetchMode("
			select r.* , concat_ws(' ',fname,lname,CompanyName) fullname
			from WAR_requests r 
			left join BSC_persons using(PersonID)
			where " . $where, $param);
	}
	
}

class WAR_periods extends OperationClass
{
	const TableName = "WAR_periods";
	const TableKey = "PeriodID";
		
	public $RequestID;
	public $PeriodID;
	public $StartDate;
	public $EndDate;
	public $wage;
	public $LetterNo;
	public $LetterDate;
	
	function __construct($RowID = "") {
		
		$this->DT_StartDate = DataMember::CreateDMA(DataMember::DT_DATE);
		$this->DT_EndDate = DataMember::CreateDMA(DataMember::DT_DATE);
		
		if($RowID != "")
			PdoDataAccess::FillObject ($this, "select * from WAR_periods where RowID=?", array($RowID));
	}
	
}
?>
