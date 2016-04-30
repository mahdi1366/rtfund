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
	public $title;
	public $FromTime;
	public $ToTime;
	public $IsActive;
			
	function __construct($ShiftID = "") {
		
		$this->DT_FromTime = DataMember::CreateDMA(DataMember::DT_TIME);
		$this->DT_ToTime = DataMember::CreateDMA(DataMember::DT_TIME);
		
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
			
	function __construct($RowID = "") {
		
		$this->DT_FromDate = DataMember::CreateDMA(DataMember::DT_DATE);
		$this->DT_ToDate = DataMember::CreateDMA(DataMember::DT_DATE);
		
		parent::__construct($RowID);
	}
	
	static function Get($where = '', $whereParams = array(), $order = "") {
		
		$query = "select ps.*,concat_ws(' ',fname,lname,CompanyName) fullname , s.title ShiftTitle
			from ATN_PersonShifts ps 
			join BSC_persons p using(PersonID) 
			join ATN_shifts s using(ShiftID) 
			where 1=1" . $where . " " . $order;
		
		return parent::runquery_fetchMode($query, $whereParams);
	}
	
	function DatesAreValid(){
		
		$dt = PdoDataAccess::runquery("select * from ATN_PersonShifts
			where PersonID=:p AND ShiftID=:s 
			AND ( (:f between FromDate AND if(ToDate='0000-00-00','4000-00-00',ToDate) )
				OR (:t between FromDate AND if(ToDate='0000-00-00','4000-00-00',ToDate) ) ) AND RowID <> :r",
				array(":p" => $this->PersonID, ":s" => $this->ShiftID, ":r" => $this->RowID,
					":f" => DateModules::shamsi_to_miladi($this->FromDate), 
					":t" => DateModules::shamsi_to_miladi($this->ToDate)));
		if(count($dt) > 0)
		{
			ExceptionHandler::PushException("شیفت انتخاب شده دارای تداخل زمانی میباشد");
			return false;
		}	
		$ShiftObj = new ATN_shifts($this->ShiftID);
		
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
		}		
		
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
	
	function Remove($pdo = null) {
		
		/*$query = "
			select * from ATN_traffic t 
				join ATN_PersonShifts ps on(t.PersonID=ps.PersonID AND t.TrafficDate between ps.FromDate AND ps.ToDate)
				join ATN_shifts t on(t.PersonID=ps.PersonID AND ps.ShiftID=t.ShiftID AND t.TrafficTime between ps.FromTime AND ps.ToTime )
			where PersonID=:p AND t.ShiftID <>
		";*/
		
		return parent::Remove($pdo);
	}
}

class ATN_traffic extends OperationClass
{
	
}

?>
