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
				select g.MenuID as GroupID,g.MenuDesc GroupDesc, g.ordering GroupOrder,g.SystemID GroupSystemID, m.*
				from FRW_menus g 
				left join FRW_menus m on(g.MenuID=m.ParentID)
				where g.parentID=0 AND m.MenuID is null AND g.SystemID=:s

				union all

				select g.MenuID as GroupID, g.MenuDesc GroupDesc,g.ordering GroupOrder,g.SystemID GroupSystemID, m.*
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

class FRW_persons extends PdoDataAccess {

	public $PersonID;
	public $UserID;
	public $UserPass;
	public $fname;
	public $lname;
	public $IsActive;
	public $PostID;

	static function GetAll($where  = "", $param = array()){
	
		return PdoDataAccess::runquery("select * from FRW_persons where 1=1" . $where , $param);
	}
	
	function AddUser() {

		$result = PdoDataAccess::insert("FRW_persons", $this);
		if ($result === false)
			return false;

		$this->PersonID = parent::InsertID();

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_add;
		$daObj->MainObjectID = $this->PersonID;
		$daObj->TableName = "FRW_persons";
		$daObj->execute();
		return true;
	}

	function EditUser() {
		
		$result = PdoDataAccess::update("FRW_persons", $this, "PersonID=:pid", array(":pid" => $this->PersonID));
		if ($result === false)
			return false;

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_update;
		$daObj->MainObjectID = $this->PersonID;
		$daObj->TableName = "FRW_persons";
		$daObj->execute();
		return true;
	}

	static public function DeleteUser($PersonID) {
		
		$result = PdoDataAccess::runquery("update FRW_persons set IsActive='NO' where PersonID=?", array($PersonID));
		if ($result === false)
			return false;

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_delete;
		$daObj->MainObjectID = $PersonID;
		$daObj->TableName = "FRW_persons";
		$daObj->execute();
		return true;
	}
	
	static public function ResetPass($PersonID) {
		
		$result = PdoDataAccess::runquery("update FRW_persons set UserPass=null where PersonID=?", array($PersonID));
		if ($result === false)
			return false;

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_update;
		$daObj->MainObjectID = $PersonID;
		$daObj->description = "پاک کردن پسورد";
		$daObj->TableName = "FRW_persons";
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
			select g.MenuID GroupID, g.MenuDesc GroupDesc, m.*, s.*
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
			join BSC_peoples a on(a.PeopleID=" . $_SESSION["USER"]["PeopleID"] . " and 
				(g.IsBorrow=a.IsBorrow OR g.IsShareholder=a.IsShareholder)	)
			
			where m.ParentID>0 AND m.IsActive='YES' AND m.SystemID=?
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

		$this->MenuID = parent::InsertID();

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
}

?>
