<?php
//---------------------------
// programmer:	Jafarkhani
// create Date:	89.10
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

class BSC_posts extends PdoDataAccess
{
	public $PostID;
	public $UnitID;
	public $PostName;

	function  __construct($PostID = "")
	{
		if($PostID != "")
			parent::FillObject($this, "select * from BSC_posts where PostID=:p",
				array(":p" => $PostID));
	}

	function AddPost()
	{
	 	if(!parent::insert("BSC_posts", $this))
			return false;
	 	
		$this->PostID = parent::InsertID();
		
	 	$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_add;
		$daObj->MainObjectID = $this->PostID;
		$daObj->TableName = "BSC_posts";
		$daObj->execute();
		return true;	
	}
	 
	function EditPost()
	{
		$whereParams = array();
	 	$whereParams[":p"] = $this->PostID;
	 	
	 	if(!parent::update("BSC_posts", $this, " PostID=:p", $whereParams))
			return false;
	 	
	 	$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_update;
		$daObj->MainObjectID = $this->PostID;
		$daObj->TableName = "BSC_posts";
		$daObj->execute();
		return true;	
	 	
	}
	 
	static function RemovePost($PostID)
	{
	 	$whereParams = array();
	 	$whereParams[":p"] = $PostID;
	 	
	 	if(!parent::delete("BSC_posts", " PostID=:p", $whereParams))
			return false;
	 	
	 	$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_delete;
		$daObj->MainObjectID = $PostID;
		$daObj->TableName = "BSC_posts";
		$daObj->execute();
		return true;
	}
}

class BSC_branches extends PdoDataAccess
{
	public $BranchID;
	public $BranchName;
	public $IsActive;
	
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


?>
