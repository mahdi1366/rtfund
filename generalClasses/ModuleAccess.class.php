<?php
//-------------------------------
// programmer	:	SH.Jafarkhani
// Date			:	90.01
//-------------------------------
require_once config::$root_path."framework_management/UserRole/UserRole.class.php";

class ModuleAccess extends PdoDataAccess
{
	public $DeputyID;
	public $ModuleID;
	public $ElementID;
	public $UserID;
	
	private $Roles;	// array of UserRole objects

	Private $AcInsert = false;
	Private $AcFullUpdate = false;
	Private $AcUpdate = false;
	Private $AcFullDelete = false;
	Private $AcDelete = false;

	public function  __construct($FacilityID, $ElementID=0, $DeputyID="", $ModuleID="")
	{
		$db = parent::getPdoObject(config::$db_servers['master']["host"],
			config::$db_servers['master']["framework_user"],config::$db_servers['master']["framework_pass"],"framework");
			
		if($DeputyID != "" && $ModuleID != "")
		{
			$this->DeputyID = $DeputyID;
			$this->ModuleID = $ModuleID;
			$this->ElementID = $ElementID;
		}
		else if(!empty($FacilityID))
		{
			$query = "select * from SystemFacilities where FacilityID=?";
			$dt = parent::runquery($query, array($FacilityID),$db);

			if(parent::GetExceptionCount() != 0)
				return;
			if(count($dt) == 0)
				return;

			$this->DeputyID = $dt[0]["DeputyID"];
			$this->ModuleID = $dt[0]["ModuleID"];
			$this->ElementID = $ElementID;
		}
		else
		{
			return;
		}
		
		$this->UserID = $_SESSION["User"]->UserID;
		$this->Roles = UserRole::GetUserRole($this->DeputyID, $this->UserID);
		if(parent::GetExceptionCount() != 0)
			return;
		
		$rolesStr = "-1";
		if(count($this->Roles) != 0)
		{
			for($i=0; $i<count($this->Roles); $i++)
			$rolesStr .= "," . $this->Roles[$i]->UserRole;
		}

		$query = "select DeputyID,ModuleID,ElementID,
						max(AcInsert) as AcInsert,
						max(AcFullUpdate) as AcFullUpdate,
						max(AcUpdate) as AcUpdate,
						max(AcFullDelete) as AcFullDelete,
						max(AcDelete) as AcDelete
				from ModuleAccess
				where (RoleID in($rolesStr) OR UserID=:uid)
					AND DeputyID=:did AND ModuleID=:mid AND ElementID=:eid
				group by DeputyID,ModuleID,ElementID";

		$dt =  parent::runquery($query, array(":uid" => $this->UserID, ":did" => $this->DeputyID,
			":mid" => $this->ModuleID, ":eid" => $this->ElementID), $db);
		
		if(parent::GetExceptionCount() != 0 || count($dt) == 0)
			return;

		$this->AcInsert = $dt[0]["AcInsert"] == 0 ? false : true;
		$this->AcFullUpdate = $dt[0]["AcFullUpdate"] == 0 ? false : true;
		$this->AcUpdate = $dt[0]["AcUpdate"] == 0 ? false : true;
		$this->AcFullDelete = $dt[0]["AcFullDelete"] == 0 ? false : true;
		$this->AcDelete = $dt[0]["AcDelete"] == 0 ? false : true;

	}

	/**
	 *
	 * @return boolean
	 */
	public function InsertAccess()
	{
		return $this->AcInsert;
	}

	/**
	 *
	 * @return boolean
	 */
	public function FullUpdateAccess()
	{
		return $this->AcFullUpdate;
	}

	/**
	 *
	 * @return boolean
	 */
	public function UpdateAccess()
	{
		return $this->AcUpdate;
	}

	/**
	 *
	 * @return boolean
	 */
	public function FullDeleteAccess()
	{
		return $this->AcFullDelete;
	}

	/**
	 *
	 * @return boolean
	 */
	public function DeleteAccess()
	{
		return $this->AcDelete;
	}

	/**
	 *
	 * @return UserRole[] array of UserRole object 
	 */
	public function GetUserRoles()
	{
		return $this->Roles;
	}

}

?>
