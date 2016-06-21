<?php
//---------------------------
// programmer:	Jafarkhani
// create Date:	90.06
//---------------------------

class manage_staff_details extends PdoDataAccess
{

	public $staff_id;
	public $FUNdescription;
	public $FUNWorkAge;
	public $FUNsbid;
	public $FUNsfid;
	public $FUNeducational_level;
	public $FUNscience_level;
	public $FUNemp_mode;
	public $FUNChildrenCount;

	function __construct($staff_id = "")
	{
		if($staff_id != "")
		{
			$whereParam = array(":staff_id" => $staff_id);
			$query = "select * from staff_details where staff_id=:staff_id";
			PdoDataAccess::FillObject($this, $query, $whereParam);
		}
	 }

	function Add($pdo="")
	{
		if( PdoDataAccess::insert("staff_details", $this, $pdo) === false )
			return false;

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_add;
		$daObj->MainObjectID = $this->staff_id;
		$daObj->TableName = "staff_details";
		$daObj->execute($pdo);

	 	return true;

	}

	function Edit()
	{

		$whereParams = array();
	 	//$whereParams[":pid"] = $this->PersonID;
	 	$whereParams[":sid"] = $this->staff_id;

	 	if(PdoDataAccess::update("staff_details",$this," staff_id =:sid ", $whereParams) === false)
	 		return false;

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_update;
		$daObj->MainObjectID = $this->staff_id;
		$daObj->TableName = "staff_details";
		$daObj->execute();

	 	return true;
	}

	static function remove($staff_id, $PersonID = "")
	{
		if($staff_id != "")
		{
			$whereParam = array(":staff_id"=> $staff_id);

			$return = PdoDataAccess::delete("staff_details","staff_id=:staff_id", $whereParam);
			if($return == "0")
				return false;

			$daObj = new DataAudit();
			$daObj->ActionType = DataAudit::Action_delete;
			$daObj->MainObjectID = $staff_id;
			$daObj->TableName = "staff_details";
			$daObj->execute();

			return true;
		}
		if($PersonID != "")
		{
			$query = "delete sd
						from staff_details sd join staff s using(staff_id) where s.PersonID=?";
			PdoDataAccess::runquery($query, array($PersonID));

			if(ExceptionHandler::GetExceptionCount() != 0)
				return false;
			return true;
		}
	 }

	 private static function LastID($pdo = "")
	 {
	 	return PdoDataAccess::GetLastID("staff", "staff_id", "", array());
	 }

}

?>