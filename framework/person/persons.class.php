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
			if(IsReal='YES',concat(fname, ' ', lname),CompanyName) fullname
			from BSC_persons 
				left join BSC_posts using(PostID)
			where " . $where, $param);
	}
	
	function AddPerson()
	{
	 	if(!parent::insert("BSC_persons",$this))
			return false;
		$this->PersonID = parent::InsertID();
		
		require_once getenv("DOCUMENT_ROOT") . '/accounting/baseinfo/baseinfo.class.php';
		$obj = new ACC_tafsilis();
		$obj->ObjectID = $this->PersonID;
		$obj->TafsiliCode = $this->PersonID;
		$obj->TafsiliDesc = $this->IsReal == "YES" ? $this->fname . " " . $this->lname : $this->CompanyName;
		$obj->TafsiliType = "1";
		$obj->AddTafsili();
		
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
		
		PdoDataAccess::runquery("update BSC_persons set IsActive='NO' where PersonID=?", array($PersonID));
		PdoDataAccess::runquery("update ACC_tafsilis set IsActive='NO' 
			where TafsiliType=1 AND ObjectID=?", array($PersonID));

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_delete;
		$daObj->MainObjectID = $PersonID;
		$daObj->TableName = "BSC_persons";
		$daObj->execute();
	 	return true;
	}
	
	static public function ResetPass($PersonID) {
		
		$hash_cost_log2 = 8;	
		$hasher = new PasswordHash($hash_cost_log2, true);
		$newPass = $hasher->HashPassword(md5("123456"));
		
		$result = PdoDataAccess::runquery("update BSC_persons set UserPass=? where PersonID=?", 
			array($newPass, $PersonID));
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
