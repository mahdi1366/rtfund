<?php
//---------------------------
// programmer:	Jafarkhani
// create Date: 95.02
//---------------------------

class ATN_traffic extends OperationClass
{
	const TableName = "ATN_traffic";
	const TableKey = "TrafficID";
	
	public $TrafficID;
	public $PersonID;
	public $TrafficDate;
	public $TrafficTime;
	public $IsSystemic;
	public $IsActive;
	public $RequestID;
	
	static function Get($where = '', $whereParams = array()) {
		
		$query = "select t.*,s.ShiftTitle , s.FromTime,s.ToTime
			
			from ATN_traffic t
			join ATN_PersonShifts ps on(ps.IsActive='YES' AND t.PersonID=ps.PersonID AND TrafficDate between FromDate AND ToDate)
			join ATN_shifts s on(ps.ShiftID=s.ShiftID)
			where 1=1 " . $where;
		
		return parent::runquery_fetchMode($query, $whereParams);		
	}
	
}


class ATN_requests extends OperationClass
{
	const TableName = "ATN_requests";
	const TableKey = "RequestID";
	
	public $RequestID;
	public $PersonID;
	public $ReqDate;
	public $FromDate;
	public $ToDate;
	public $StartTime;
	public $EndTime;
	public $ReqType;
	public $ReqStatus;
	public $details;
	
	public $MissionPlace;
	public $MissionSubject;
	public $MissionStay;
	public $GoMean;
	public $ReturnMean;
	public $OffType;
	public $OffPersonID;
	
	public $SurveyPersonID;
	public $SurveyDate;
	
	public $_fullname;
	public $_GoMeanDesc;
	public $_ReturnMeanDesc;
	
	function __construct($id = '') {
		
		$this->DT_ReqDate = DataMember::CreateDMA(DataMember::DT_DATE);
		$this->DT_FromDate = DataMember::CreateDMA(DataMember::DT_DATE);
		$this->DT_ToDate = DataMember::CreateDMA(DataMember::DT_DATE);
		$this->DT_SurveyDate = DataMember::CreateDMA(DataMember::DT_DATE);
		
		$this->DT_StartTime = DataMember::CreateDMA(DataMember::DT_TIME);
		$this->DT_EndTime = DataMember::CreateDMA(DataMember::DT_TIME);
		
		parent::FillObject($this, "select t.*,concat(fname,' ',lname) _fullname,
				bf1.InfoDesc _GoMeanDesc,bf2.InfoDesc _ReturnMeanDesc
			from ATN_requests t join BSC_persons using(PersonID)
				left join BaseInfo bf1 on(bf1.TypeID=21 AND bf1.InfoID=GoMean)
				left join BaseInfo bf2 on(bf2.TypeID=21 AND bf2.InfoID=ReturnMean)
			where RequestID=?", array($id));
	}
	
	static function Get($where = '', $whereParams = array()) {
		
		$query = "select t.*,concat(p1.fname,' ',p1.lname) fullname,
				bf1.InfoDesc GoMeanDesc,bf2.InfoDesc ReturnMeanDesc,bf3.InfoDesc OffTypeDesc,
				concat(p2.fname,' ',p2.lname) OffFullname,
				concat(p3.fname,' ',p3.lname) SurveyFullname
			from ATN_requests t
			join BSC_persons p1 using(PersonID)
			left join BSC_persons p2 on(OffPersonID=p2.PersonID)
			left join BSC_persons p3 on(SurveyPersonID=p3.PersonID)
			left join BaseInfo bf1 on(bf1.TypeID=21 AND bf1.InfoID=GoMean)
			left join BaseInfo bf2 on(bf2.TypeID=21 AND bf2.InfoID=ReturnMean)
			left join BaseInfo bf3 on(bf3.TypeID=20 AND bf3.InfoID=OffType)
			where 1=1 " . $where;
		
		return parent::runquery_fetchMode($query, $whereParams);		
	}
}
?>
