<?php
//---------------------------
// programmer:	Jafarkhani
// create Date: 94.06
//---------------------------

class BSC_peoples extends PdoDataAccess
{
	public $PeopleID;
	public $UserName;
	public $UserPass;
	public $fullname;
	public $NationalID;
	public $EconomicID;
	public $PhoneNo;
	public $mobile;
	public $address;
	public $email;
	public $IsBorrow;
	public $IsShareholder;
	public $IsActive;
			
	function __construct($PeopleID = "") {
		
		if($PeopleID != "")
			PdoDataAccess::FillObject ($this, "select * from BSC_peoples where PeopleID=?", array($PeopleID));
	}
	
	static function SelectAll($where = "", $param = array()){
		
		return PdoDataAccess::runquery_fetchMode("select * from BSC_peoples where " . $where, $param);
	}
	
	function AddPeople()
	{
	 	if(!parent::insert("BSC_peoples",$this))
			return false;
		$this->PeopleID = parent::InsertID();
		
		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_add;
		$daObj->MainObjectID = $this->PeopleID;
		$daObj->TableName = "BSC_peoples";
		$daObj->execute();
		return true;
	}
	
	function EditPeople()
	{
	 	if( parent::update("BSC_peoples",$this," PeopleID=:l", array(":l" => $this->PeopleID)) === false )
	 		return false;

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_update;
		$daObj->MainObjectID = $this->PeopleID;
		$daObj->TableName = "BSC_peoples";
		$daObj->execute();
	 	return true;
    }
	
	static function DeletePeople($PeopleID){
		
		if( parent::delete("BSC_peoples"," PeopleID=?", array($PeopleID)) === false )
	 		return false;

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_delete;
		$daObj->MainObjectID = $PeopleID;
		$daObj->TableName = "BSC_peoples";
		$daObj->execute();
	 	return true;
	}
}
?>
