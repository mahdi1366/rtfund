<?php
//---------------------------
// programmer:	Jafarkhani
// create Date: 94.06
//---------------------------

class ATN_shifts extends OperationClass
{
	const TableName = "ATN_shifts";
	const TableKey = "ShiftID";
	
	public $ShiftID;
	public $ShiftTitle;
	public $FromTime;
	public $ToTime;
	public $IsActive;
	public $ExceptFromTime;
	public $ExceptToTime;
			
	function __construct($ShiftID = "") {
		
		$this->DT_FromTime = DataMember::CreateDMA(DataMember::DT_TIME);
		$this->DT_ToTime = DataMember::CreateDMA(DataMember::DT_TIME);
		$this->DT_ExceptFromTime = DataMember::CreateDMA(DataMember::DT_TIME);
		$this->DT_ExceptToTime = DataMember::CreateDMA(DataMember::DT_TIME);
		
		if($ShiftID != "")
			PdoDataAccess::FillObject ($this, "select *	from ATN_shifts where ShiftID=?", array($ShiftID));
	}
	
	function Remove(){
		
		parent::Remove();
		
	}
}

class ATN_PersonShifts extends OperationClass
{
	const TableName = "ATN_PersonShifts";
	const TableKey = "RowID";
	
	public $RowID;
	public $PersonID;
	public $ShiftID;
	public $FromDate;
	public $ToDate;
	public $IsActive;
			
	function __construct($RowID = "") {
		
		$this->DT_FromDate = DataMember::CreateDMA(DataMember::DT_DATE);
		$this->DT_ToDate = DataMember::CreateDMA(DataMember::DT_DATE);
		
		parent::__construct($RowID);
	}
	
	static function Get($where = '', $whereParams = array(), $order = "") {
		
		$query = "select ps.*,concat_ws(' ',fname,lname,CompanyName) fullname , s.ShiftTitle
			from ATN_PersonShifts ps 
			join BSC_persons p using(PersonID) 
			join ATN_shifts s using(ShiftID) 
			where 1=1" . $where . " " . $order;
		
		return parent::runquery_fetchMode($query, $whereParams);
	}
	
	function DatesAreValid(){
		
		$dt = PdoDataAccess::runquery("select * from ATN_PersonShifts
			where PersonID=:p 
			AND ( :f between FromDate AND ToDate OR :t between FromDate AND ToDate ) AND RowID <> :r",
				array(":p" => $this->PersonID, ":r" => $this->RowID,
					":f" => DateModules::shamsi_to_miladi($this->FromDate, "-"), 
					":t" => DateModules::shamsi_to_miladi($this->ToDate, "-")));
		if(count($dt) > 0)
		{
			ExceptionHandler::PushException("شیفت انتخاب شده دارای تداخل زمانی میباشد");
			return false;
		}	
		/*$ShiftObj = new ATN_shifts($this->ShiftID);
		
		$dt = PdoDataAccess::runquery("select * from ATN_PersonShifts join ATN_shifts using(ShiftID)
			where PersonID=:p 
			AND ( (:s between FromTime AND ToTime) OR (:e between FromTime AND ToTime) ) AND RowID <> :r 
			AND ( (:f between FromDate AND if(ToDate='0000-00-00','4000-00-00',ToDate) ) 
				OR (:t between FromDate AND if(ToDate='0000-00-00','4000-00-00',ToDate) ) )", 
			array(":p" => $this->PersonID, ":s" => $ShiftObj->FromTime, 
				  ":e" => $ShiftObj->ToTime, ":r" => $this->RowID,
				  ":f" => DateModules::shamsi_to_miladi($this->FromDate), 
				  ":t" => DateModules::shamsi_to_miladi($this->ToDate)));

		if(count($dt) > 0)
		{
			ExceptionHandler::PushException("شیفت های این فرد با یکدیگر دارای تداخل ساعتی می باشند");;
			return false;
		}		*/
		
		return true;
	}
	
	function Add($pdo = null) {
		
		if(!$this->DatesAreValid())
			return false;
		return parent::Add($pdo);
	}
	
	function Edit($pdo = null) {
		
		if(!$this->DatesAreValid())
			return false;
		return parent::Edit($pdo);
	}
	
	static function GetShiftOfDate($PersonID, $date){
		
		$query = "select s.*

		from ATN_PersonShifts ps
		left join ATN_requests r on(ReqType='CHANGE_SHIFT' and ReqStatus=2 and r.FromDate=:d
		  and ps.PersonID=r.PersonID)

		join ATN_shifts s on(ifnull(r.ShiftID,ps.ShiftID)=s.ShiftID)

		where ps.IsActive='YES' AND ps.PersonID=:p AND :d between ps.FromDate AND ps.ToDate";
		
		$dt = parent::runquery($query, array(":p" => $PersonID, ":d" => $date));
		return count($dt) > 0 ? $dt[0] : null;
	}
	
}

class ATN_holidays extends OperationClass
{
	const TableName = "ATN_holidays";
	const TableKey = "HolidayID";
	
	public $HolidayID;
	public $TheDate;
	public $details;
	
	function __construct($id = '') {
		
		$this->DT_TheDate = DataMember::CreateDMA(DataMember::DT_DATE);
		
		parent::__construct($id);
	}
}

class ATN_settings extends OperationClass
{
	const TableName = "ATN_settings";
	const TableKey = "RowID";
	
	public $RowID;
	public $StartDate;
	public $EndDate;
	public $telorance;
	public $MaxDayExtra;
	public $MaxMonthExtra;
	
	function __construct($id = '') {
		
		$this->DT_StartDate = DataMember::CreateDMA(DataMember::DT_DATE);
		$this->DT_EndDate = DataMember::CreateDMA(DataMember::DT_DATE);
		
		parent::__construct($id);
	}
}


?>
