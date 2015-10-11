<?php
//---------------------------
// programmer:	Jafarkhani
// create Date: 94.06
//---------------------------

class BSC_persons extends PdoDataAccess
{
	public $PersonID;
	public $UserName;
	public $UserPass;
	public $IsReal;
	public $fname;
	public $lname;
	public $CompanyName;
	public $NationalID;
	public $EconomicID;
	public $PhoneNo;
	public $mobile;
	public $address;
	public $email;
	
	public $IsCustomer;
	public $IsShareholder;
	public $IsAgent;
	public $IsSupporter;
	public $IsStaff;
	
	public $IsActive;
	public $PostID;
			
	function __construct($PersonID = "") {
		
		if($PersonID != "")
			PdoDataAccess::FillObject ($this, "select * from BSC_persons where PersonID=?", array($PersonID));
	}
	
	static function SelectAll($where = "", $param = array()){
		
		return PdoDataAccess::runquery_fetchMode("select *, '' UserPass,
			concat(fname, ' ', lname) fullname 
			from BSC_persons 
				left join BSC_posts using(PostID)
			where " . $where, $param);
	}
	
	function AddPerson()
	{
	 	if(!parent::insert("BSC_persons",$this))
			return false;
		$this->PersonID = parent::InsertID();
		
		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_add;
		$daObj->MainObjectID = $this->PersonID;
		$daObj->TableName = "BSC_persons";
		$daObj->execute();
		return true;
	}
	
	function EditPerson()
	{
	 	if( parent::update("BSC_persons",$this," PersonID=:l", array(":l" => $this->PersonID)) === false )
	 		return false;

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_update;
		$daObj->MainObjectID = $this->PersonID;
		$daObj->TableName = "BSC_persons";
		$daObj->execute();
	 	return true;
    }
	
	static function DeletePerson($PersonID){
		
		if( parent::delete("BSC_persons"," PersonID=?", array($PersonID)) === false )
	 		return false;

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_delete;
		$daObj->MainObjectID = $PersonID;
		$daObj->TableName = "BSC_persons";
		$daObj->execute();
	 	return true;
	}
	
	static public function ResetPass($PersonID) {
		
		$result = PdoDataAccess::runquery("update BSC_persons set UserPass=null where PersonID=?", array($PersonID));
		if ($result === false)
			return false;

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_update;
		$daObj->MainObjectID = $PersonID;
		$daObj->description = "پاک کردن پسورد";
		$daObj->TableName = "BSC_persons";
		$daObj->execute();
		return true;
	}
}
?>
