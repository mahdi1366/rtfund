<?php
//---------------------------
// developer:	Sh.Jafarkhani
// Date:		97.05
//---------------------------

class COM_processes extends PdoDataAccess {

    public $ProcessID;
    public $ParentID;
    public $ProcessTitle;
    public $IsActive;
    public $description;

    function __construct() {
        $this->DT_ProcessID = DataMember::CreateDMA(DataMember::Pattern_Num);
        $this->DT_ParentID = DataMember::CreateDMA(DataMember::Pattern_Num);
        $this->DT_ProcessTitle = DataMember::CreateDMA(DataMember::Pattern_FaEnAlphaNum);
        $this->DT_EventID = DataMember::CreateDMA(DataMember::Pattern_Num);
        $this->DT_IsActive = DataMember::CreateDMA(DataMember::Pattern_EnAlphaNum);
        $this->DT_description = DataMember::CreateDMA(DataMember::Pattern_FaEnAlphaNum);
    }

    static function SelectProcesss($where = '', $param = array(), $hasChid = false) {

        $query = " select p.*, e.EventTitle
				from COM_processes p";

        if ($hasChid)
            $query .= " join COM_processes p2 on(p.ProcessID=p2.ParentID)";

        $query .= " left join COM_events e on(e.EventID=p.EventID)";

        if ($where != '')
            $query .= ' where ' . $where;

        //if($hasChid)
        $query .= " group by p.ProcessID";

        $res = parent::runquery($query, $param);

        return $res;
    }

    function InsertProcess() {

        if (!parent::insert("COM_processes", $this))
            return false;

        $this->ProcessID = parent::InsertID();

        $daObj = new DataAudit();
        $daObj->ActionType = DataAudit::Action_add;
        $daObj->MainObjectID = $this->ProcessID;
        $daObj->TableName = "COM_processes";
        $daObj->execute();

        return true;
    }

    function UpdateProcess($old_ProcessID) {

        if (!parent::update("COM_processes", $this, 'ProcessID=:EID', array(':EID' => (int)$old_ProcessID)))
            return false;

        if ($old_ProcessID != $this->ProcessID) {
            PdoDataAccess::runquery("update COM_processes set parentID=:new where parentID=:old",
                    array(":new" =>(int) $this->ProcessID, ":old" =>(int) $old_ProcessID));
        }

        $daObj = new DataAudit();
        $daObj->ActionType = DataAudit::Action_update;
        $daObj->MainObjectID = $old_ProcessID;
        $daObj->TableName = "COM_processes";
        $daObj->execute();

        return true;
    }

    static function DeleteProcess($ProcessID) {

        parent::delete("COM_processes", "ProcessID=:EID", array(':EID' =>(int) $ProcessID));
        if (parent::AffectedRows() == 0)
            $res = parent::runquery("update COM_processes set IsActive='NO' where ProcessID=:EID", array(':EID' =>(int) $ProcessID));

        if ($res === false)
            return false;

        if (parent::AffectedRows()) {

            $daObj = new DataAudit();
            $daObj->ActionType = DataAudit::Action_delete;
            $daObj->MainObjectID = $ProcessID;
            $daObj->TableName = "COM_processes";
            $daObj->execute();
        }
        return true;
    }

}

class COM_sharing extends OperationClass {
	
	const TableName = "COM_sharing";
	const TableKey = "ShareID"; 
 
    public $ProcessID;
    public $ShareID;
    public $CostID;
    public $ShareType;
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
					concat_ws('',cb1.blockCode,cb2.blockCode,cb3.blockCode) CostCode,
					concat_ws(' ',fname,lname,CompanyName) changePersonName,
					bf.InfoDesc BaseDesc,
					p.PostName
					
			from COM_sharing s 
			left join BSC_persons on(PersonID=ChangePersonID)
			join  ACC_CostCodes cc using(CostID)
			left join ACC_blocks cb1 on(cb1.blockID=cc.level1)
			left join ACC_blocks cb2 on(cb2.blockID=cc.level2)
			left join ACC_blocks cb3 on(cb3.blockID=cc.level3)
			left join BaseInfo bf on(bf.TypeID=85 AND bf.InfoID=BaseID)
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

class COM_events extends PdoDataAccess {

    public $EventID;
    public $ParentID;
    public $EventTitle;
    public $IsActive;

    function __construct() {
        $this->DT_EventID = DataMember::CreateDMA(DataMember::Pattern_Num);
        $this->DT_ParentID = DataMember::CreateDMA(DataMember::Pattern_Num);
        $this->DT_EventTitle = DataMember::CreateDMA(DataMember::Pattern_FaEnAlphaNum);
        $this->DT_IsActive = DataMember::CreateDMA(DataMember::Pattern_EnAlphaNum);
    }

    static function SelectEvents($where = '', $param = array()) {

        $query = " select * from COM_events ";
        if ($where != '')
            $query .= ' where ' . $where;

        return parent::runquery($query, $param);
    }

    function InsertEvent() {

        if (!parent::insert("COM_events", $this))
            return false;

        $this->EventID = parent::InsertID();

        $daObj = new DataAudit();
        $daObj->ActionType = DataAudit::Action_add;
        $daObj->MainObjectID = $this->EventID;
        $daObj->TableName = "COM_events";
        $daObj->execute();

        return true;
    }

    function UpdateEvent($old_EventID) {

        if (!parent::update("COM_events", $this, 'EventID=:EID', array(':EID' => (int)$old_EventID)))
            return false;

        if ($old_EventID != $this->EventID) {
            PdoDataAccess::runquery("update COM_events set parentID=:new where parentID=:old", array(":new" =>(int) $this->EventID, ":old" =>(int) $old_EventID));
        }

        $daObj = new DataAudit();
        $daObj->ActionType = DataAudit::Action_update;
        $daObj->MainObjectID = $old_EventID;
        $daObj->TableName = "COM_events";
        $daObj->execute();

        return true;
    }

    static function DeleteEvent($EventID) {

        parent::delete("COM_events", "EventID=:EID", array(':EID' => (int)$EventID));
        if (parent::AffectedRows() == 0)
            $res = parent::runquery("update COM_events set IsActive='NO' where EventID=:EID", array(':EID' =>(int) $EventID));

        if ($res === false)
            return false;

        if (parent::AffectedRows()) {

            $daObj = new DataAudit();
            $daObj->ActionType = DataAudit::Action_delete;
            $daObj->MainObjectID = $EventID;
            $daObj->TableName = "COM_events";
            $daObj->execute();
        }
        return true;
    }

}
 
class COM_EventRows extends PdoDataAccess {

    public $RowID;
    public $EventID;
    public $CostID;
    public $TafsiliType;
	public $TafsiliType2;
	public $TafsiliType3;
    public $CostType;
    public $DocDesc;
    public $IsActive;
    public $ChangeDate;
    public $NewRowID;
    public $ChangeDesc;
    public $ChangePersonID;
	public $ComputeItemID;

    function __construct() {
        $this->DT_RowID = DataMember::CreateDMA(DataMember::Pattern_Num);
        $this->DT_EventID = DataMember::CreateDMA(DataMember::Pattern_Num);
        $this->DT_OfficeID = DataMember::CreateDMA(DataMember::Pattern_Num);
        $this->DT_TafsiliType = DataMember::CreateDMA(DataMember::Pattern_Num);
        $this->DT_CostType = DataMember::CreateDMA(DataMember::Pattern_EnAlphaNum);
        $this->DT_PriceDesc = DataMember::CreateDMA(DataMember::Pattern_EnAlphaNum);
        $this->DT_DocDesc = DataMember::CreateDMA(DataMember::Pattern_FaEnAlphaNum);
        $this->DT_AmountPercent = DataMember::CreateDMA(DataMember::Pattern_Num);
        $this->DT_IsActive = DataMember::CreateDMA(DataMember::Pattern_EnAlphaNum);
        $this->DT_ChangeDate = DataMember::CreateDMA(DataMember::Pattern_Date);
        $this->DT_NewRowID = DataMember::CreateDMA(DataMember::Pattern_FaEnAlphaNum);
        $this->DT_ChangeDesc = DataMember::CreateDMA(DataMember::Pattern_FaEnAlphaNum);
        $this->DT_ChangePersonID = DataMember::CreateDMA(DataMember::Pattern_Num);
    }

    static function SelectAll($where = '', $param = array()) {

        $query = " select er.*,
					bf.InfoDesc TafsiliTypeDesc,
					bf2.InfoDesc TafsiliType2Desc,
					bf3.InfoDesc TafsiliType3Desc,
					concat_ws('-',cb1.blockDesc,cb2.blockDesc,cb3.blockDesc) CostDesc,
					concat_ws('',cb1.blockCode,cb2.blockCode,cb3.blockCode) CostCode,
					concat_ws(' ',fname,lname,CompanyName) changePersonName,
					concat(bf4.InfoDesc,' - ',bf3.InfoDesc) ComputeItemDesc
					
			from COM_EventRows er 
			left join BSC_persons on(PersonID=ChangePersonID)
			left join BaseInfo bf on(bf.TypeID=2 AND bf.InfoID=er.TafsiliType)
			left join BaseInfo bf2 on(bf2.TypeID=2 AND bf2.InfoID=er.TafsiliType2)
			left join BaseInfo bf3 on(bf3.TypeID=2 AND bf3.InfoID=er.TafsiliType3)
			join  ACC_CostCodes cc using(CostID)
			left join ACC_blocks cb1 on(cb1.blockID=cc.level1)
			left join ACC_blocks cb2 on(cb2.blockID=cc.level2)
			left join ACC_blocks cb3 on(cb3.blockID=cc.level3)
			left join BaseInfo bf3 on(bf3.TypeID=84 AND bf3.InfoID=er.ComputeItemID)
			left join BaseInfo bf4 on(bf4.TypeID=83 AND bf3.param1=bf4.InfoID)";

        if ($where != '')
            $query .= ' where ' . $where;

        return parent::runquery($query, $param);
    }

    function InsertEventRow($pdo = null) {

        $this->ChangeDate = PDONOW;
        $this->ChangePersonID =(int) $_SESSION["USER"]["PersonID"];

        if (!parent::insert("COM_EventRows", $this, $pdo))
            return false;

        $this->RowID = parent::InsertID($pdo);

        $daObj = new DataAudit();
        $daObj->ActionType = DataAudit::Action_add;
        $daObj->MainObjectID = $this->RowID;
        $daObj->TableName = "COM_EventRows";
        $daObj->execute($pdo);

        return true;
    }

    function UpdateEventRow($pdo = null) {

        $this->ChangeDate = PDONOW;
        $this->ChangePersonID = (int)$_SESSION["USER"]["PersonID"];

        if (!parent::update("COM_EventRows", $this, 'RowID=:RID', array(':RID' => $this->RowID), $pdo))
            return false;

        $daObj = new DataAudit();
        $daObj->ActionType = DataAudit::Action_update;
        $daObj->MainObjectID = $this->RowID;
        $daObj->TableName = "COM_EventRows";
        $daObj->execute($pdo);

        return true;
    }
}
?>
