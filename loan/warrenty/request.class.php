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
	public $StartDate;
	public $EndDate;
	public $wage;
	public $LetterNo;
	public $LetterDate;
	public $StatusID;
	public $ReqVersion;
	public $IsBlock;
	
	public $_fullname;
	public $_TypeDesc;
	
	function __construct($RequestID = "") {
		
		$this->DT_ReqDate = DataMember::CreateDMA(DataMember::DT_DATE);
		$this->DT_StartDate = DataMember::CreateDMA(DataMember::DT_DATE);
		$this->DT_EndDate = DataMember::CreateDMA(DataMember::DT_DATE);
		$this->DT_LetterDate = DataMember::CreateDMA(DataMember::DT_DATE);
		
		if($RequestID != "")
			PdoDataAccess::FillObject ($this, "
				select r.* , concat_ws(' ',fname,lname,CompanyName) _fullname,bf.InfoDesc _TypeDesc
					from WAR_requests r 
					left join BaseInfo bf on(bf.TypeID=74 AND InfoID=r.TypeID)
					left join BSC_persons using(PersonID)
				where RequestID=?", array($RequestID));
	}
	
	static function SelectAll($where = "", $param = array(), $order = ""){
		
		return PdoDataAccess::runquery_fetchMode("
			select r.* , concat_ws(' ',fname,lname,CompanyName) fullname, sp.StepDesc,
				bf.InfoDesc TypeDesc,d.DocID,d.LocalNo, d.DocStatus 
			from WAR_requests r 
				left join BSC_persons using(PersonID)
				left join BaseInfo bf on(bf.TypeID=74 AND InfoID=r.TypeID)
				join WFM_FlowSteps sp on(sp.FlowID=" . FLOWID . " AND sp.StepID=r.StatusID)
				left join ACC_DocItems on(SourceType='" . DOCTYPE_WARRENTY . "' 
					AND SourceID=r.RequestID AND SourceID2=r.ReqVersion)	
				left join ACC_docs d using(DocID)
			where " . $where . 
			" group by r.RequestID" . $order, $param);
	}
	
	static function ChangeStatus($RequestID, $StatusID, $ActDesc = "", $LogOnly = false, $pdo = null){
	
		if(!$LogOnly)
		{
			$obj = new WAR_requests();
			$obj->RequestID = $RequestID;
			$obj->StatusID = $StatusID;
			if(!$obj->Edit($pdo))
				return false;
		}

		return WFM_FlowRows::AddOuterFlow(FLOWID, $RequestID, $StatusID, $ActDesc = "", $pdo);
	}
	
	function GetAccDoc($pdo = null){
		
		$dt = PdoDataAccess::runquery("
			select DocID from ACC_DocItems where SourceType=" . DOCTYPE_WARRENTY . " 
			AND SourceID=? AND SourceID2=?" , array($this->RequestID, $this->ReqVersion), $pdo);
		if(count($dt) == 0)
			return 0;
		return $dt[0][0];
	}
}
?>
