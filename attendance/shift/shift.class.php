<?php
//---------------------------
// programmer:	Jafarkhani
// create Date: 94.06
//---------------------------

class ATS_shifts extends PdoDataAccess
{
	public $ShiftID;
	public $GroupID;
	public $title;
	public $FromTime;
	public $ToTime;
	public $IsActive;
			
	function __construct($ShiftID = "") {
		
		if($ShiftID != "")
			PdoDataAccess::FillObject ($this, "select *	from ATN_shifts where ShiftID=?", array($ShiftID));
	}
	
	static function SelectAll($where = "", $param = array()){
		
		return PdoDataAccess::runquery_fetchMode("select l.*,InfoDesc GroupDesc from ATN_shifts l
			join BaseInfo bf on(bf.TypeID=17 AND bf.InfoID=l.GroupID)
			where " . $where, $param);
	}
	
	function AddShift()
	{
	 	if(!parent::insert("ATN_shifts",$this))
			return false;
		$this->ShiftID = parent::InsertID();
		
		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_add;
		$daObj->MainObjectID = $this->ShiftID;
		$daObj->TableName = "ATN_shifts";
		$daObj->execute();
		return true;
	}
	
	function EditShift()
	{
	 	if( parent::update("ATN_shifts",$this," ShiftID=:l", array(":l" => $this->ShiftID)) === false )
	 		return false;

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_update;
		$daObj->MainObjectID = $this->ShiftID;
		$daObj->TableName = "ATN_shifts";
		$daObj->execute();
	 	return true;
    }
	
	static function DeleteShift($ShiftID){
		
		if( parent::delete("ATN_shifts"," ShiftID=?", array($ShiftID)) === false )
	 		return false;

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_delete;
		$daObj->MainObjectID = $ShiftID;
		$daObj->TableName = "ATN_shifts";
		$daObj->execute();
	 	return true;
	}
}

?>
