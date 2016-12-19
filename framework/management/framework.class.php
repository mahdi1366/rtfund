<?php

//-------------------------
// programmer:	Jafarkhani
// Create Date:	89.11
//-------------------------

class FRW_systems extends PdoDataAccess {

	public $SystemID;
	public $SysName;
	public $SysPath;
	public $SysIcon;
	public $IsActive;

	public function __construct($SystemID = "") {
		if ($SystemID == "")
			return;

		parent::FillObject($this, "select * from FRW_systems where SystemID=?", array($SystemID));
	}

	static function GetAll($where  = "", $param = array()){
	
		return PdoDataAccess::runquery("select * from FRW_systems where 1=1" . $where , $param);
	}
	
	function AddSystem() {

		$result = PdoDataAccess::insert("FRW_systems", $this);
		if ($result === false)
			return false;

		$this->SystemID = parent::InsertID();

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_add;
		$daObj->MainObjectID = $this->SystemID;
		$daObj->TableName = "FRW_systems";
		$daObj->execute();
		return true;
	}

	function EditSystem() {
		
		$result = PdoDataAccess::update("FRW_systems", $this, "SystemID=:pid", array(":pid" => $this->SystemID));
		if ($result === false)
			return false;

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_update;
		$daObj->MainObjectID = $this->SystemID;
		$daObj->TableName = "FRW_systems";
		$daObj->execute();
		return true;
	}

}

class FRW_Menus extends PdoDataAccess {

	public $SystemID;
	public $MenuID;
	public $ParentID;
	public $MenuDesc;
	public $IsActive;
	public $ordering;
	public $icon;
	public $MenuPath;

	public function __construct($MenuID = "") {
		if ($MenuID == "")
			return;

		parent::FillObject($this, "select * from FRW_menus where MenuID=?", array($MenuID));
	}

	static function GetAllMenus($SystemID) {
		$query = "
			select * from (
				select g.MenuID as GroupID,g.MenuDesc GroupDesc, g.ordering GroupOrder,
					g.icon GroupIcon,
					g.SystemID GroupSystemID, m.*
				from FRW_menus g 
				left join FRW_menus m on(g.MenuID=m.ParentID)
				where g.parentID=0 AND m.MenuID is null AND g.SystemID=:s

				union all

				select g.MenuID as GroupID, g.MenuDesc GroupDesc,g.ordering GroupOrder,
					g.icon GroupIcon,g.SystemID GroupSystemID, m.*
				from FRW_menus m
				join FRW_menus g on(m.ParentID=g.MenuID)
				where m.ParentID>0 AND m.SystemID=:s 
			)t 
			order by GroupOrder,ordering";
		return parent::runquery($query, array(":s" => $SystemID));
	}

	function AddMenu() {
		$result = PdoDataAccess::insert("FRW_menus", $this);
		if ($result === false)
			return false;

		$this->MenuID = parent::InsertID();

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_add;
		$daObj->MainObjectID = $this->MenuID;
		$daObj->TableName = "FRW_menus";
		$daObj->execute();
		return true;
	}

	function EditMenu() {
		$result = PdoDataAccess::update("FRW_menus", $this, "MenuID=:mid", array(":mid" => $this->MenuID));
		if ($result === false)
			return false;

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_update;
		$daObj->MainObjectID = $this->MenuID;
		$daObj->TableName = "FRW_menus";
		$daObj->execute();
		return true;
	}

	static public function DeleteMenu($MenuID) {
		$result = PdoDataAccess::delete("FRW_menus", "MenuID=?", array($MenuID));
		if ($result === false)
			return false;

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_delete;
		$daObj->MainObjectID = $MenuID;
		$daObj->TableName = "FRW_menus";
		$daObj->execute();
		return true;
	}

}

class FRW_access extends PdoDataAccess {

	public $MenuID;
	public $PersonID;
	public $ViewFlag;
	public $AddFlag;
	public $EditFlag;
	public $RemoveFlag;

	static function getAccessMenus($SystemID) {
		
		return parent::runquery("
			select g.MenuID GroupID, g.MenuDesc GroupDesc, g.icon GroupIcon, m.*, s.*
			from FRW_menus m 
			join FRW_menus g on(m.ParentID=g.MenuID)
			join FRW_systems s on(m.SystemID=s.SystemID)
			join FRW_access a on(a.personID=" . $_SESSION["USER"]["PersonID"] . " and m.MenuID=a.MenuID)
			
			where m.ParentID>0 AND m.IsActive='YES' AND m.SystemID=?
			order by g.ordering,m.ordering", array($SystemID));
	}
	
	static function getPortalMenus($SystemID) {
		
		return parent::runquery("
			select g.MenuID GroupID, g.MenuDesc GroupDesc, m.*, s.*
			from FRW_menus m 
			join FRW_menus g on(m.ParentID=g.MenuID)
			join FRW_systems s on(m.SystemID=s.SystemID)
			join BSC_persons a on(a.PersonID=" . $_SESSION["USER"]["PersonID"] . " and 
				(
					if(g.IsCustomer='YES',a.IsCustomer='YES',0=1) OR 
					if(g.IsShareholder='YES',a.IsShareholder='YES',0=1) OR 
					if(g.IsStaff='YES',a.IsStaff='YES',0=1)	OR
					if(g.IsAgent='YES',a.IsAgent='YES',0=1)	OR
					if(g.IsExpert='YES',a.IsExpert='YES',0=1)	OR
					if(g.IsSupporter='YES',a.IsSupporter='YES',0=1)	
				)
			)
			
			where m.ParentID>0 AND a.IsActive='YES' AND m.IsActive='YES' AND m.SystemID=?
			order by g.ordering,m.ordering", array($SystemID));
	}

	static function getAccessSystems() {
		return parent::runquery("
			select s.*, group_concat(MenuDesc ORDER BY MenuDesc SEPARATOR '<br>' ) menuNames
			from FRW_access a
			join FRW_menus using(MenuID)
			join FRW_systems s using(SystemID)
			
			where a.PersonID=" . $_SESSION["USER"]["PersonID"] . "
			group by SystemID
			order by SysName");
	}

	static function selectAccess($SystemID, $PersonID) {
		$query = "
			select g.MenuID GroupID, g.MenuDesc GroupDesc, m.*, a.ViewFlag,a.AddFlag,a.EditFlag,a.RemoveFlag
			from FRW_menus m 
			join FRW_menus g on(m.ParentID=g.MenuID)
			left join FRW_access a on(a.personID=? and m.MenuID=a.MenuID)
			
			where m.SystemID=? AND m.ParentID>0 
			order by g.ordering,m.ordering";

		return parent::runquery($query, array($PersonID, $SystemID));
	}

	function AddAccess() {

		$result = PdoDataAccess::insert("FRW_access", $this);
		if ($result === false)
			return false;

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_add;
		$daObj->MainObjectID = $this->MenuID;
		$daObj->SubObjectID = $this->PersonID;
		$daObj->TableName = "FRW_access";
		$daObj->execute();
		return true;

		return true;
	}

	static function GetAccess($MenuID){
		
		$obj = new FRW_access();
		PdoDataAccess::FillObject($obj, "select * from FRW_access where MenuID=? AND PersonID=?",
				array($MenuID, $_SESSION["USER"]["PersonID"]));
		
		$obj->ViewFlag =	$obj->ViewFlag == "YES" ? true : false;
		$obj->AddFlag =		$obj->AddFlag == "YES" ? true : false;
		$obj->EditFlag =	$obj->EditFlag == "YES" ? true : false;
		$obj->RemoveFlag =	$obj->RemoveFlag == "YES" ? true : false;
		
		return $obj;
	}

	static function GetAccessBranches(){
		
		$dt = PdoDataAccess::runquery("select BranchID from BSC_BranchAccess where PersonID=?",
			array($_SESSION["USER"]["PersonID"]));
		
		$arr = array();
		foreach($dt as $row)
			$arr[] = $row["BranchID"];
		
		return $arr;				
	}
	
}

class FRW_tasks extends PdoDataAccess {

	public $TaskID;
	public $SystemID;
	public $RegPersonID;
	public $CreateDate;
	public $title;
	public $details;
	public $TaskStatus;
	public $DoneDate;
	public $DoneDesc;

	public function __construct($SystemID = "") {
		if ($SystemID == "")
			return;

		parent::FillObject($this, "select * from FRW_systems where SystemID=?", array($SystemID));
	}

	function AddTask() {

		$result = PdoDataAccess::insert("FRW_tasks", $this);
		if ($result === false)
			return false;

		$this->TaskID = parent::InsertID();

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_add;
		$daObj->MainObjectID = $this->TaskID;
		$daObj->TableName = "FRW_tasks";
		$daObj->execute();
		return true;
	}

	function EditTask() {
		
		$result = PdoDataAccess::update("FRW_tasks", $this, "TaskID=:pid", array(":pid" => $this->TaskID));
		if ($result === false)
			return false;

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_update;
		$daObj->MainObjectID = $this->TaskID;
		$daObj->TableName = "FRW_tasks";
		$daObj->execute();
		return true;
	}

	static public function DeleteTask($TaskID) {
		
		$result = PdoDataAccess::delete("FRW_tasks", "TaskID=?", array($TaskID));
		if ($result === false)
			return false;

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_delete;
		$daObj->MainObjectID = $TaskID;
		$daObj->TableName = "FRW_tasks";
		$daObj->execute();
		return true;
	}
}

class FRW_phonebook extends OperationClass {
	
	const TableName = "FRW_phonebook";
	const TableKey = "RowID"; 

	public $RowID;
	public $PersonID;
	public $fullname;
	public $phone;
	public $mobile;
	public $address;
	public $details;
	public $email;
	public $ActInfo;
	public $IsPublic;
}

?>
