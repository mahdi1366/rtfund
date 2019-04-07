<?php
//---------------------------
// programmer:	Jafarkhani
// create Date: 94.06
//---------------------------

require_once DOCUMENT_ROOT . '/accounting/baseinfo/baseinfo.class.php';
require_once DOCUMENT_ROOT . '/framework/person/persons.class.php';

class BSC_units extends PdoDataAccess {
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

class BSC_posts extends OperationClass {
	
	const TableName = "BSC_posts";
	const TableKey = "PostID";
	
	public $PostID;
	public $PostName;
	public $MissionSigner;
	public $IsActive;
	
	public function Remove($pdo = null) {
		
		$dt = parent::runquery("select * from BSC_jobs where PostID=?", array($this->PostID), $pdo);
		if(count($dt) > 0)
		{
			ExceptionHandler::PushException("برای این پست شغل تعریف شده و قابل حذف نمی باشد");
			return false;
		}
		
		$this->IsActive = "NO";
		return $this->Edit($pdo);
	}

	public static function GetMissionSigner(){
		
		$dt = PdoDataAccess::runquery("select PostID,PostName,concat_ws(' ',fname,lname) fullname, PersonID
			from BSC_posts 
				join BSC_jobs using(PostID) 
				join BSC_persons using(PersonID) 
			where MissionSigner='YES' AND IsMain='YES'");
		if(count($dt) > 0)
			return $dt[0];
		return false;
	}
	
}

class BSC_jobs extends OperationClass {
	
	const TableName = "BSC_jobs";
	const TableKey = "JobID";
	
	public $JobID;
	public $PostID;
	public $UnitID;
	public $PersonID;
	public $IsMain;
	
	public function CheckUniqueMainJob(){
		
		if($this->IsMain != "YES")
			return true;
		
		$dt = PdoDataAccess::runquery("select * from BSC_jobs where PersonID=? AND IsMain='YES' AND JobID<>?",
			array($this->PersonID, $this->JobID));
		if(count($dt) > 0)
		{
			ExceptionHandler::PushException("هر فرد تنها یک شغل اصلی می تواند داشته باشد");
			return false;
		}
		return true;
	}
	
	public function Add($pdo = null) {
		
		if(!$this->CheckUniqueMainJob())
			return false;
		return parent::Add($pdo);
	}
	
	public function Edit($pdo = null) {
		
		if(!$this->CheckUniqueMainJob())
			return false;
		return parent::Edit($pdo);
	}
	
	static public function GetModirAmelPerson(){
		
		$dt = PdoDataAccess::runquery("select PersonID from BSC_jobs where PostID=? AND IsMain='YES'",
				array(POSTID_MODIRAMEL));
		if(count($dt) == 0)
			return new BSC_persons();
		return new BSC_persons($dt[0]["PersonID"]);
	}
}

class BSC_branches extends PdoDataAccess {
	public $BranchID;
	public $BranchName;
	public $IsActive;
	public $DefaultBankTafsiliID;
	public $DefaultAccountTafsiliID;
	public $WarrentyAllowed;
	
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

class BSC_setting extends OperationClass {
	const TableName = "BSC_setting";
	const TableKey = "ParamID";
	
	public $ParamID;
	public $SystemID;
	public $ParamTitle;
	public $ParamValues;
	public $ParamDesc;
	public $ParamValue;
}

class BSC_CheckLists extends OperationClass {
	const TableName = "BSC_CheckLists";
	const TableKey = "ItemID";
	
	public $ItemID;
	public $SourceType;
	public $ItemDesc;
	public $ordering;
}

class BaseInfo extends PdoDataAccess {

	public $TypeID;
	public $InfoID;
	public $InfoDesc;
	public $IsActive;
	public $param1;
	public $param2;
	public $param3;
	public $param4;
	public $param5;
	public $param6;
	public $param7;
	
	public $_TableName;
	public $_FieldName;
	
	function __construct($TypeID = "", $infoID = "") {
		
		if($TypeID != "")
		{
			return parent::FillObject($this, "select i.*, TableName _TableName, FieldName _FieldName "
					. "from BaseInfo i join BaseTypes using(TypeID)"
					. "where i.TypeID=? AND i.InfoID=?", 
				array($TypeID , $infoID));
		}
	}
	
	function Add($pdo = null){
		
		if(empty($this->InfoID))
		{ 
			$this->InfoID = PdoDataAccess::GetLastID("BaseInfo", "InfoID", "TypeID=?", array($this->TypeID), $pdo);
			$this->InfoID = $this->InfoID*1 + 1;
		}
		
		if( parent::insert("BaseInfo", $this, $pdo) === false )
		    return false;

	    $daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_add;
		$daObj->MainObjectID = $this->TypeID;
		$daObj->SubObjectID = $this->InfoID;
		$daObj->TableName = "BaseInfo";
		$daObj->execute($pdo);
		return true;	
    }

    function Edit($OldInfoID = "", $pdo = null){
		
	    $whereParams = array();
	    $whereParams[":tid"] = $this->TypeID;
		$whereParams[":fid"] = $OldInfoID == "" ? $this->InfoID : $OldInfoID;

	    if( parent::update("BaseInfo",$this," TypeID=:tid and InfoID=:fid", $whereParams, $pdo) === false )
		    return false;

	    $daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_update;
		$daObj->MainObjectID = $this->TypeID;
		$daObj->SubObjectID = $this->InfoID;
		$daObj->TableName = "BaseInfo";
		$daObj->execute($pdo);
		return true;	
    }

    function Remove(){
		
		if($this->_FieldName == "" || $this->_TableName == "")
		{
			$this->IsActive = "NO";
			return $this->Edit();
		}
	    $dt = PdoDataAccess::runquery("select count(*) from " . $this->_TableName . 
				" where " . $this->_FieldName . "=?", array($this->InfoID));
		if($dt[0][0]*1 == 0)
		{
			parent::delete("BaseInfo", "TypeID=? AND InfoID=?", array($this->TypeID, $this->InfoID));
			$daObj = new DataAudit();
			$daObj->ActionType = DataAudit::Action_delete;
			$daObj->MainObjectID = $this->TypeID;
			$daObj->SubObjectID = $this->InfoID;
			$daObj->TableName = "BaseInfo";
			$daObj->execute();
			return true;	
		}
		
		$this->IsActive = "NO";
		return $this->Edit();
    }

}

class BSC_processes extends OperationClass {

	const TableName = "BSC_processes";
	const TableKey = "ProcessID"; 
 
    public $ProcessID;
    public $ParentID;
    public $ProcessTitle;
    public $IsActive;
    public $description;
	public $FlowID;
	
	public function __construct($id = '') {
		
		$this->DT_ProcessID = DataMember::CreateDMA(DataMember::Pattern_Num);
        $this->DT_ParentID = DataMember::CreateDMA(DataMember::Pattern_Num);
        $this->DT_ProcessTitle = DataMember::CreateDMA(DataMember::Pattern_FaEnAlphaNum);
        $this->DT_EventID = DataMember::CreateDMA(DataMember::Pattern_Num);
        $this->DT_IsActive = DataMember::CreateDMA(DataMember::Pattern_EnAlphaNum);
        $this->DT_description = DataMember::CreateDMA(DataMember::Pattern_FaEnAlphaNum);
		
		parent::__construct($id);
	}
	
    static function SelectProcesss($where = '', $param = array(), $hasChid = false) {

        $query = " select p.*, e.EventTitle
				from BSC_processes p";

        if ($hasChid)
            $query .= " join BSC_processes p2 on(p.ProcessID=p2.ParentID)";

        $query .= " left join COM_events e on(e.EventID=p.EventID)";

        if ($where != '')
            $query .= ' where ' . $where;

        //if($hasChid)
        $query .= " group by p.ProcessID";

        $res = parent::runquery($query, $param);

        return $res;
    }

    function InsertProcess() {

        if (!parent::insert("BSC_processes", $this))
            return false;

        $this->ProcessID = parent::InsertID();

        $daObj = new DataAudit();
        $daObj->ActionType = DataAudit::Action_add;
        $daObj->MainObjectID = $this->ProcessID;
        $daObj->TableName = "BSC_processes";
        $daObj->execute();

		$obj = new ACC_tafsilis();
		$obj->ObjectID = $this->ProcessID;
		$obj->TafsiliCode = $this->ProcessID;
		$obj->TafsiliDesc = $this->ProcessTitle;
		$obj->TafsiliType = TAFSILITYPE_PROCESS;
		$obj->AddTafsili();
		
        return true;
    }

    function UpdateProcess($old_ProcessID) {

        if (!parent::update("BSC_processes", $this, 'ProcessID=:EID', array(':EID' => (int)$old_ProcessID)))
            return false;

        if ($old_ProcessID != $this->ProcessID) {
            PdoDataAccess::runquery("update BSC_processes set parentID=:new where parentID=:old",
                    array(":new" =>(int) $this->ProcessID, ":old" =>(int) $old_ProcessID));
        }

        $daObj = new DataAudit();
        $daObj->ActionType = DataAudit::Action_update;
        $daObj->MainObjectID = $old_ProcessID;
        $daObj->TableName = "BSC_processes";
        $daObj->execute();
		
		$dt = PdoDataAccess::runquery("select * from ACC_tafsilis "
				. "where ObjectID=? AND TafsiliType=" . TAFSILITYPE_PROCESS, array($old_ProcessID));
		
		if(count($dt) == 0)
		{
			$obj = new ACC_tafsilis();
			$obj->ObjectID = $this->ProcessID;
			$obj->TafsiliCode = $this->ProcessID;
			$obj->TafsiliDesc =  $this->ProcessTitle;
			$obj->TafsiliType = TAFSILITYPE_PROCESS;
			$obj->AddTafsili();
		}
		else
		{
			$obj = new ACC_tafsilis($dt[0]["TafsiliID"]);
			$obj->ObjectID = $this->ProcessID;
			$obj->TafsiliCode = $this->ProcessID;
			$obj->TafsiliDesc = $this->LoanDesc;
			$obj->EditTafsili();
		}

        return true;
    }

    static function DeleteProcess($ProcessID) {

		$obj = new BSC_processes($ProcessID);
		$obj->IsActive = "NO";
		return $obj->UpdateProcess($obj->ProcessID);
    }

}

class COM_sharing extends OperationClass {
	
	const TableName = "COM_sharing";
	const TableKey = "ShareID"; 
 
    public $ProcessID;
    public $ShareID;
    public $CostID;
    public $ShareType;
	public $MethodID;
	public $BaseID;
	public $BaseValue;
	public $PostID;
	
	public $IsActive;
    public $ChangeDate;
    public $ChangeDesc;
    public $ChangePersonID;
	
	static function Get($where = '', $param = array(), $pdo = null) {

        $query = " select s.*,
					concat_ws('-',cb1.blockDesc,cb2.blockDesc,cb3.blockDesc) CostDesc,
					cc.CostCode,
					concat_ws(' ',fname,lname,CompanyName) changePersonName,
					bf.InfoDesc BaseDesc,
					bf2.InfoDesc MethodDesc,
					p.PostName
					
			from COM_sharing s 
			left join BSC_persons on(PersonID=ChangePersonID)
			join  ACC_CostCodes cc using(CostID)
			left join ACC_blocks cb1 on(cb1.blockID=cc.level1)
			left join ACC_blocks cb2 on(cb2.blockID=cc.level2)
			left join ACC_blocks cb3 on(cb3.blockID=cc.level3)
			left join BaseInfo bf on(bf.TypeID=85 AND bf.InfoID=BaseID)
			left join BaseInfo bf2 on(bf2.TypeID=86 AND bf2.InfoID=MethodID)
			
			left join BSC_posts p on(s.PostID=p.PostID)
			
			where 1=1 " . $where;

        return parent::runquery_fetchMode($query, $param, $pdo);
    }
	
	function Add($pdo = null) {
		
		$this->ChangeDate = PDONOW;
        $this->ChangePersonID =(int) $_SESSION["USER"]["PersonID"];
		return parent::Add($pdo);
	}
	
	function Edit($pdo = null) {
		
		$this->ChangeDate = PDONOW;
        $this->ChangePersonID =(int) $_SESSION["USER"]["PersonID"];
		return parent::Edit($pdo);
	}
	
	function Remove($pdo = null) {
		
		$this->IsActive = "NO";
		return $this->Edit($pdo);
	}
}
?>
