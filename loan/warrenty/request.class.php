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
	public $SubjectDesc;
	public $SubjectNO;
	public $organization;
	public $ReqDate;
	public $amount;
	public $StartDate;
	public $EndDate;
	public $CancelDate;
	public $wage;
	public $LetterNo;
	public $LetterDate;
	public $StatusID;
	public $RefRequestID;
	public $version;
	public $IsBlock;
	public $BranchID;
	public $RegisterAmount;
	public $SavePercent;
	
	
	public $_fullname;
	public $_TypeDesc;
	
	function __construct($RequestID = "", $pdo = null) {
		
		$this->DT_ReqDate = DataMember::CreateDMA(DataMember::DT_DATE);
		$this->DT_StartDate = DataMember::CreateDMA(DataMember::DT_DATE);
		$this->DT_EndDate = DataMember::CreateDMA(DataMember::DT_DATE);
		$this->DT_LetterDate = DataMember::CreateDMA(DataMember::DT_DATE);
		$this->DT_CancelDate = DataMember::CreateDMA(DataMember::DT_DATE);
		
		if($RequestID != "")
			PdoDataAccess::FillObject ($this, "
				select r.* , concat_ws(' ',fname,lname,CompanyName) _fullname,bf.InfoDesc _TypeDesc
					from WAR_requests r 
					left join BaseInfo bf on(bf.TypeID=74 AND InfoID=r.TypeID)
					left join BSC_persons using(PersonID)
				where RequestID=?", array($RequestID), $pdo);
	}
	
	static function SelectAll($where = "", $param = array(), $order = ""){
		
		return PdoDataAccess::runquery_fetchMode("
			select r.* , concat_ws(' ',fname,lname,CompanyName) fullname, 
				p.address,
				p.NationalID,
				p.PhoneNo,
				p.mobile,
				bf.InfoDesc TypeDesc,d.DocID,group_concat(distinct d.LocalNo) LocalNo, d.StatusID DocStatusID , 
				BranchName,
				if(lst.RequestID=r.RequestID, 'YES', 'NO') IsCurrent,
				concat(if(fr.ActionType='REJECT','رد ',''),sp.StepDesc) StepDesc,
				if(sp.StepID=1 AND fr.ActionType='REJECT', 'YES', 'NO') ResendEnable,
				fr.ActionType
				
			from WAR_requests r 
				left join BSC_persons p using(PersonID)
				join BSC_branches b using(BranchID)
				left join BaseInfo bf on(bf.TypeID=74 AND InfoID=r.TypeID)
				join WFM_FlowSteps sp on(sp.FlowID=" . FLOWID_WARRENTY . " AND sp.StepID=r.StatusID)
				left join WFM_FlowRows fr on(fr.IsLastRow='YES' AND fr.ObjectID=r.RequestID 
					AND fr.StepRowID=sp.StepRowID AND fr.FlowID=sp.FlowID)
			
				left join ACC_DocItems on(r.RequestID=SourceID2 AND 
					SourceType in(" . DOCTYPE_WARRENTY . ",".DOCTYPE_WARRENTY_END.",".DOCTYPE_WARRENTY_EXTEND."))
				left join ACC_docs d using(DocID)
				left join (
						select max(RequestID) RequestID,RefRequestID
						from WAR_requests 
						group by RefRequestID
				)lst on(lst.RefRequestID=r.RefRequestID)
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

		return WFM_FlowRows::AddOuterFlow(FLOWID_WARRENTY, $RequestID, $StatusID, $ActDesc = "", $pdo);
	}
	
	function GetAccDoc($pdo = null){
		
		$dt = PdoDataAccess::runquery("
			select DocID from ACC_DocItems where SourceType=" . DOCTYPE_WARRENTY . " 
			AND SourceID2=?" , array($this->RequestID), $pdo);
		if(count($dt) == 0)
			return 0;
		return $dt[0][0];
	}
}

class WAR_costs extends OperationClass
{
	const TableName = "WAR_costs";
	const TableKey = "CostID";
	
	public $CostID;
	public $RequestID;
	public $CostDesc;
	public $CostAmount;
	public $CostCodeID;
	public $CostType;

	public static function Get($where = '', $whereParams = array()) {
		
		$query = "select c.*,cc.CostCode , 
				concat_ws(' - ',b1.BlockDesc,b2.BlockDesc,b3.BlockDesc,b4.BlockDesc) CostCodeDesc
			from WAR_costs c
			left join ACC_CostCodes cc on(c.CostCodeID=cc.CostID)
			left join ACC_blocks b1 on(level1=b1.BlockID)
			left join ACC_blocks b2 on(level2=b2.BlockID)
			left join ACC_blocks b3 on(level3=b3.BlockID)
			left join ACC_blocks b4 on(level4=b4.BlockID)
			where 1=1 " . $where;
		
		return PdoDataAccess::runquery_fetchMode($query, $whereParams);
	}
}

class WAR_guarantors extends OperationClass{
	
	const TableName = "WAR_guarantors";
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
