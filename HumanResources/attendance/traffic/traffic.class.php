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
	
	static function Get($where = '', $whereParams = array()) {
		
		$query = "select t.*,s.ShiftTitle from ATN_traffic t
			join ATN_PersonShifts ps on(ps.IsActive='YES' AND t.PersonID=ps.PersonID AND TrafficDate between FromDate AND ToDate)
			join ATN_shifts s on(ps.ShiftID=s.ShiftID)
			where 1=1 " . $where;
		
		return parent::runquery_fetchMode($query, $whereParams);		
	}
}

?>
