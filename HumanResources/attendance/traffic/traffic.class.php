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
	
	function __construct($id = '') {
		
		$this->DT_ReqDate = DataMember::CreateDMA(DataMember::DT_DATE);
		$this->DT_FromDate = DataMember::CreateDMA(DataMember::DT_DATE);
		$this->DT_ToDate = DataMember::CreateDMA(DataMember::DT_DATE);
		$this->DT_SurveyDate = DataMember::CreateDMA(DataMember::DT_DATE);
		
		$this->DT_StartTime = DataMember::CreateDMA(DataMember::DT_TIME);
		$this->DT_EndTime = DataMember::CreateDMA(DataMember::DT_TIME);
		
		parent::__construct($id);
	}
	
	static function Get($where = '', $whereParams = array()) {
		
		$query = "select t.*,concat(fname,' ',lname) fullname
			from ATN_requests t
			join BSC_persons using(PersonID)
			where 1=1 " . $where;
		
		return parent::runquery_fetchMode($query, $whereParams);		
	}
}
?>
