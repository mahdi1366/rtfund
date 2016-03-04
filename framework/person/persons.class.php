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
	public $RegNo;
	public $RegDate;
	public $RegPlace;
	public $CompanyType;
	public $AccountNo;
	public $WebSite;
	public $IsGovermental;
	public $FatherName;
	public $ShNo;
	
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
		
		return PdoDataAccess::runquery_fetchMode("select p.*, '' UserPass,
			if(IsReal='YES',concat(fname, ' ', lname),CompanyName) fullname, DomainDesc
			from BSC_persons p
				left join BSC_posts using(PostID)
				left join BSC_ActDomain using(DomainID)
			where " . $where, $param);
	}
	
	function AddPerson()
	{
		$dt = PdoDataAccess::runquery("select * 
			from BSC_persons where UserName=?", array($this->UserName));
		if(count($dt) > 0)
		{
			ExceptionHandler::PushException("شناسه وارد شده تکراری است");
			return false;
		}
		
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
		$dt = PdoDataAccess::runquery("select * 
			from BSC_persons where PersonID<>? AND UserName=?", array($this->PersonID, $this->UserName));
		if(count($dt) > 0)
		{
			ExceptionHandler::PushException("شناسه وارد شده تکراری است");
			return false;
		}
		
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

class BSC_OrgSigners extends PdoDataAccess{
	
	public $RowID;
    public $PersonID;
	public $fullname;
	public $sex;
	public $NationalID;
	public $telephone;
	public $mobile;
	public $PostDesc;
	public $detail;

    static function GetAll($where = "",$whereParam = array()){
	    $query = "select * from BSC_OrgSigners ";
	    $query .= ($where != "") ? " where " . $where : "";
	    return parent::runquery($query, $whereParam);
    }
	
    function AddSigner($pdo = null){
	    if( parent::insert("BSC_OrgSigners", $this, $pdo) === false )
		    return false;

	    $this->RowID = parent::InsertID($pdo);

	    $daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_add;
		$daObj->MainObjectID = $this->RowID;
		$daObj->SubObjectID = $this->PersonID;
		$daObj->TableName = "BSC_OrgSigners";
		$daObj->execute($pdo);
		return true;	
    }
	
	function EditSigner($pdo = null){
	    if( parent::update("BSC_OrgSigners", $this, "RowID=:s", array(":s" =>$this->RowID), $pdo) === false )
		    return false;

	    $daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_update;
		$daObj->MainObjectID = $this->RowID;
		$daObj->SubObjectID = $this->PersonID;
		$daObj->TableName = "BSC_OrgSigners";
		$daObj->execute($pdo);
		return true;	
    }
	
	static function DeleteSigner($RowID){
		
	    if( parent::delete("BSC_OrgSigners", "RowID=?", array($RowID)) === false )
		    return false;

	    $daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_delete;
		$daObj->MainObjectID = $RowID;
		$daObj->TableName = "BSC_OrgSigners";
		$daObj->execute();
		return true;	
    }
	
}

class BSC_licenses extends PdoDataAccess{
	
	public $LicenseID;
    public $PersonID;
	public $title;
	public $LicenseNo;
	public $ExpDate;
	public $IsConfirm;
	public $ConfirmPersonID;
	public $RejectDesc;

	function __construct(){
		
		$this->DT_ExpDate = DataMember::CreateDMA(DataMember::DT_DATE);
	}
	
    static function GetAll($where = "",$whereParam = array()){
	    $query = "select * from BSC_licenses ";
	    $query .= ($where != "") ? " where " . $where : "";
	    return parent::runquery($query, $whereParam);
    }
	
    function AddLicense($pdo = null){
	    if( parent::insert("BSC_licenses", $this, $pdo) === false )
		    return false;

	    $this->LicenseID = parent::InsertID($pdo);

	    $daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_add;
		$daObj->MainObjectID = $this->LicenseID;
		$daObj->SubObjectID = $this->PersonID;
		$daObj->TableName = "BSC_licenses";
		$daObj->execute($pdo);
		return true;	
    }
	
	function EditLicense($pdo = null){
	    if( parent::update("BSC_licenses", $this, "LicenseID=:s", array(":s" =>$this->LicenseID), $pdo) === false )
		    return false;

	    $daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_update;
		$daObj->MainObjectID = $this->LicenseID;
		$daObj->SubObjectID = $this->PersonID;
		$daObj->TableName = "BSC_licenses";
		$daObj->execute($pdo);
		return true;	
    }
	
	static function DeleteLicense($LicenseID){
		
	    if( parent::delete("BSC_licenses", "LicenseID=?", array($LicenseID)) === false )
		    return false;

	    $daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_delete;
		$daObj->MainObjectID = $LicenseID;
		$daObj->TableName = "BSC_licenses";
		$daObj->execute();
		return true;	
    }
	
}

?>
