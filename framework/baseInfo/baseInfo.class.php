<?php
//---------------------------
// programmer:	Jafarkhani
// create Date: 94.06
//---------------------------

class BSC_units extends PdoDataAccess
{
	public $UnitID;
	public $ParentID;
	public $UnitName;

	function  __construct($UnitID = "")
	{
		if($UnitID != "")
			parent::FillObject($this, "select * from BSC_units where UnitID=:UnitID",
				array(":UnitID" => $UnitID));
	}

	function AddUnit()
	{
	 	if(!parent::insert("BSC_units", $this))
			return false;
	 	
		$this->UnitID = parent::InsertID();
		
	 	$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_add;
		$daObj->MainObjectID = $this->UnitID;
		$daObj->TableName = "BSC_units";
		$daObj->execute();
		return true;	
	}
	 
	function EditUnit()
	{
		$whereParams = array();
	 	$whereParams[":ouid"] = $this->UnitID;
	 	
	 	if(!parent::update("BSC_units", $this, " UnitID=:ouid", $whereParams))
			return false;
	 	
	 	$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_update;
		$daObj->MainObjectID = $this->UnitID;
		$daObj->TableName = "BSC_units";
		$daObj->execute();
		return true;	
	 	
	}
	 
	static function RemoveUnit($UnitID)
	{
	 	$whereParams = array();
	 	$whereParams[":ouid"] = $UnitID;
	 	
	 	if(!parent::delete("BSC_units", " UnitID=:ouid", $whereParams))
			return false;
	 	
	 	$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_delete;
		$daObj->MainObjectID = $UnitID;
		$daObj->TableName = "BSC_units";
		$daObj->execute();
		return true;
	}
}

class BSC_posts extends OperationClass
{
	const TableName = "BSC_posts";
	const TableKey = "PostID";
	
	public $PostID;
	public $PostName;
	public $MissionSigner;
	public $IsActive;
	
	public function Remove($pdo = null) {
		
		$dt = parent::runquery("select * from BSC_persons where PostID=?", array($this->PostID), $pdo);
		if(count($dt) > 0)
		{
			ExceptionHandler::PushException("این پست به فردی نسبت داده شده است و قابل حذف نمی باشد");
			return false;
		}
		
		$this->IsActive = "NO";
		return $this->Edit($pdo);
	}

	public static function GetMissionSigner(){
		
		$dt = PdoDataAccess::runquery("select PostID,PostName,concat_ws(' ',fname,lname) fullname, PersonID
			from BSC_posts join BSC_persons using(PostID) where MissionSigner='YES'");
		if(count($dt) > 0)
			return $dt[0];
		return false;
	}
	
}

class BSC_branches extends PdoDataAccess
{
	public $BranchID;
	public $BranchName;
	public $IsActive;
	public $DefaultBankTafsiliID;
	public $DefaultAccountTafsiliID;
	
	function  __construct($BranchID = "")
	{
		if($BranchID != "")
			parent::FillObject($this, "select * from BSC_branches where BranchID=:p",
				array(":p" => $BranchID));
	}

	function AddBranch()
	{
	 	if(!parent::insert("BSC_branches", $this))
			return false;
	 	
		$this->BranchID = parent::InsertID();
		
	 	$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_add;
		$daObj->MainObjectID = $this->BranchID;
		$daObj->TableName = "BSC_branches";
		$daObj->execute();
		return true;	
	}
	 
	function EditBranch()
	{
		$whereParams = array();
	 	$whereParams[":p"] = $this->BranchID;
	 	
	 	if(!parent::update("BSC_branches", $this, " BranchID=:p", $whereParams))
			return false;
	 	
	 	$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_update;
		$daObj->MainObjectID = $this->BranchID;
		$daObj->TableName = "BSC_branches";
		$daObj->execute();
		return true;	
	 	
	}
	 
	static function RemoveBranch($BranchID)
	{
	 	if(!parent::runquery("update BSC_branches set IsActive='NO' where BranchID=?", array($BranchID)))
			return false;
	 	
	 	$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_delete;
		$daObj->MainObjectID = $BranchID;
		$daObj->TableName = "BSC_branches";
		$daObj->execute();
		return true;
	}
}

class BSC_ActDomain extends PdoDataAccess{
	
	public $DomainID;
    public $ParentID;
	public $DomainDesc;
	public $PersonID;
	
    function AddDomain($pdo = null){
	    if( parent::insert("BSC_ActDomain", $this, $pdo) === false )
		    return false;

	    $this->DomainID = parent::InsertID($pdo);

	    $daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_add;
		$daObj->MainObjectID = $this->DomainID;
		$daObj->TableName = "BSC_ActDomain";
		$daObj->execute($pdo);
		return true;	
    }
	
	function EditDomain($pdo = null){
	    if( parent::update("BSC_ActDomain", $this, "DomainID=:s", array(":s" =>$this->DomainID), $pdo) === false )
		    return false;

	    $daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_update;
		$daObj->MainObjectID = $this->DomainID;
		$daObj->TableName = "BSC_ActDomain";
		$daObj->execute($pdo);
		return true;	
    }
	
	static function DeleteDomain($DomainID){
		
	    if( parent::delete("BSC_ActDomain", "DomainID=?", array($DomainID)) === false )
		    return false;

	    $daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_delete;
		$daObj->MainObjectID = $DomainID;
		$daObj->TableName = "BSC_ActDomain";
		$daObj->execute();
		return true;	
    }
	
}

class BSC_ExpertDomain extends OperationClass{
	
	const TableName = "BSC_ExpertDomain";
	const TableKey = "DomainID";
	
	public $DomainID;
    public $ParentID;
	public $DomainDesc;
	public $PersonID;
}

class BSC_PersonExpertDomain extends OperationClass{
	
	const TableName = "BSC_PersonExpertDomain";
	const TableKey = "RowID";
	
	public $RowID;
	public $PersonID;
	public $DomainID;
}

class BSC_setting extends OperationClass
{
	const TableName = "BSC_setting";
	const TableKey = "ParamID";
	
	public $ParamID;
	public $SystemID;
	public $ParamTitle;
	public $ParamValues;
	public $ParamDesc;
	public $ParamValue;
}

?>
