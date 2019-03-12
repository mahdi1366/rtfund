<?php
//---------------------------
// developer:	Sh.Jafarkhani
// Date:		97.05
//---------------------------

require_once getenv("DOCUMENT_ROOT") . '/accounting/baseinfo/baseinfo.class.php';

class COM_events extends PdoDataAccess {

    public $EventID;
    public $ParentID;
    public $EventTitle;
    public $IsActive;
	public $ordering;

    function __construct($id = "") {
        $this->DT_EventID = DataMember::CreateDMA(DataMember::Pattern_Num);
        $this->DT_ParentID = DataMember::CreateDMA(DataMember::Pattern_Num);
        $this->DT_EventTitle = DataMember::CreateDMA(DataMember::Pattern_FaEnAlphaNum);
        $this->DT_IsActive = DataMember::CreateDMA(DataMember::Pattern_EnAlphaNum);
		
		if ($id != '') {
            parent::FillObject($this, "select * from COM_events where EventID =:id", array(":id" => $id));
        }
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
	public $Tafsili;
	public $Tafsili2;
	public $Tafsili3;
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
					concat_ws('-',cb1.blockDesc,cb2.blockDesc,cb3.blockDesc) CostDesc,
					concat_ws('',cb1.blockCode,cb2.blockCode,cb3.blockCode) CostCode,
					concat_ws(' ',fname,lname,CompanyName) changePersonName,
					concat(bf11.InfoDesc,' - ',bf10.InfoDesc) ComputeItemFullDesc,
					bf10.InfoDesc ComputeItemDesc
					
					
			from COM_EventRows er 
			left join BSC_persons on(PersonID=ChangePersonID)
			join  ACC_CostCodes cc using(CostID)
			left join ACC_blocks cb1 on(cb1.blockID=cc.level1)
			left join ACC_blocks cb2 on(cb2.blockID=cc.level2)
			left join ACC_blocks cb3 on(cb3.blockID=cc.level3)
			left join BaseInfo bf10 on(bf10.TypeID=84 AND bf10.InfoID=er.ComputeItemID)
			left join BaseInfo bf11 on(bf11.TypeID=83 AND bf10.param1=bf11.InfoID)
			";

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
