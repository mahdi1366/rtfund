<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 94.08
//-----------------------------

class WFM_flows extends PdoDataAccess {

	public $FlowID;
	public $ObjectType;
	public $FlowDesc;
	public $IsSystemic;

	function __construct($FlowID = "") {
		if($FlowID != "")
			parent::FillObject ($this, "select * from WFM_flows where FlowID=?", array($FlowID));
	}
	
	static function GetAll($where = "", $whereParam = array()) {
		
		$query = "select f.*, InfoDesc ObjectDesc
			from WFM_flows f
			join BaseInfo b on(TypeID=11 AND ObjectType=InfoID)";
		
		$query .= ($where != "") ? " where " . $where : "";
		
		return parent::runquery_fetchMode($query, $whereParam);
	}

	function AddFlow($pdo = null) {
		
		if (!parent::insert("WFM_flows", $this, $pdo)) {
			return false;
		}

		$this->FlowID = parent::InsertID($pdo);

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_add;
		$daObj->MainObjectID = $this->FlowID;
		$daObj->TableName = "WFM_flows";
		$daObj->execute($pdo);

		return true;
	}

	function EditFlow($pdo = null) {
		
		if (parent::update("WFM_flows", $this, " FlowID=:fid", array(":fid" => $this->FlowID), $pdo) === false)
			return false;

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_update;
		$daObj->MainObjectID = $this->FlowID;
		$daObj->TableName = "WFM_flows";
		$daObj->execute($pdo);
		
		return true;
	}
	
	static function RemoveFlow($FlowID){
		
	 	if(!parent::delete("WFM_flows", " FlowID=?", array($FlowID)))
			return false;
	 	
	 	$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_delete;
		$daObj->MainObjectID = $FlowID;
		$daObj->TableName = "WFM_flows";
		$daObj->execute();
		return true;
	}
}

class WFM_FlowSteps extends PdoDataAccess {

	public $FlowID;
	public $StepID;
	public $StepDesc;
	public $PostID;

	static function GetAll($where = "", $whereParam = array()) {
		
		$query = "select * from WFM_FlowSteps join BSC_posts using(PostID)";
		$query .= ($where != "") ? " where " . $where : "";
		
		return parent::runquery($query, $whereParam);
	}

	function AddFlowStep($pdo = null) {
		
		if (!parent::insert("WFM_FlowSteps", $this, $pdo)) {
			return false;
		}

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_add;
		$daObj->MainObjectID = $this->FlowID;
		$daObj->SubObjectID = $this->StepID;
		$daObj->TableName = "WFM_FlowSteps";
		$daObj->execute($pdo);

		return true;
	}

	function EditFlowStep($oldStepID, $pdo = null) {
		
		if (parent::update("WFM_FlowSteps", $this, " FlowID=:fid AND StepID=:sid", 
				array(":fid" => $this->FlowID, ":sid" => $oldStepID), $pdo) === false)
			return false;

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_update;
		$daObj->MainObjectID = $this->FlowID;
		$daObj->SubObjectID = $this->StepID;
		$daObj->TableName = "WFM_FlowSteps";
		$daObj->execute($pdo);
		
		return true;
	}
	
	static function RemoveFlowStep($FlowID, $StepID){
		
	 	if(!parent::delete("WFM_FlowSteps", " FlowID=? AND StepID=?", array($FlowID, $StepID)))
			return false;
		
		PdoDataAccess::runquery("update WFM_FlowSteps set StepID=StepID-1 where StepID>? AND FlowID=?",
			array($StepID, $FlowID));
	 	
	 	$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_delete;
		$daObj->MainObjectID = $FlowID;
		$daObj->SubObjectID = $StepID;
		$daObj->TableName = "WFM_FlowSteps";
		$daObj->execute();
		return true;
	}
}

class WFM_FlowRows extends PdoDataAccess {

	public $RowID;
	public $FlowID;
	public $StepID;
	public $ObjectID;
	public $PersonID;
	public $ActionDate;
	public $ActionType;
	public $ActionComment;

	function __construct($RowID = "") {
		if($RowID != "")
			parent::FillObject ($this, "select * from WFM_FlowRows where RowID=?", array($RowID));
	}
	
	static function GetAll($where = "", $whereParam = array()) {
		
		$query = "select sd.*, 
			if(IsReal='YES',concat(fname, ' ', lname),CompanyName) fullname
			from WFM_FlowRows sd
			join BSC_persons p using(PersonID)";
		
		$query .= ($where != "") ? " where " . $where : "";
		
		return parent::runquery_fetchMode($query, $whereParam);
	}

	function AddFlowRow($pdo = null) {
		
		if (!parent::insert("WFM_FlowRows", $this, $pdo)) {
			return false;
		}

		$this->RowID = parent::InsertID($pdo);

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_add;
		$daObj->MainObjectID = $this->RowID;
		$daObj->TableName = "WFM_FlowRows";
		$daObj->execute($pdo);

		return true;
	}

	function EditFlowRow($pdo = null) {
		
		if (parent::update("WFM_FlowRows", $this, " RowID=:did", array(":did" => $this->RowID), $pdo) === false)
			return false;

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_update;
		$daObj->MainObjectID = $this->RowID;
		$daObj->TableName = "WFM_FlowRows";
		$daObj->execute($pdo);
		
		return true;
	}
	
	static function StartFlow($FlowID, $ObjectID){
		
		$obj = new WFM_FlowRows();
		$obj->FlowID = $FlowID;
		$obj->ObjectID = $ObjectID;
		$obj->PersonID = $_SESSION["USER"]["PersonID"];
		$obj->StepID = 0;
		$obj->ActionDate = PDONOW;
		$obj->ActionType = "CONFIRM";
		return $obj->AddFlowRow();		
	}
	
	static function IsFlowStarted($FlowID, $ObjectID){
		
		$dt = PdoDataAccess::runquery("select * from WFM_FlowRows "
			. "where FlowID=? AND ObjectID=?", array($FlowID, $ObjectID));
		
		return (count($dt) > 0);
	}
	
	static function IsFlowEnded($FlowID, $ObjectID){
		
		$dt = PdoDataAccess::runquery("select max(StepID) from WFM_FlowSteps where FlowID=?",
			array($FlowID));
		
		$dt = PdoDataAccess::runquery("select * from WFM_FlowRows "
			. "where FlowID=? AND ObjectID=? AND StepID=? AND ActionType='CONFIRM'",
			array($FlowID, $ObjectID, $dt[0][0]));
		
		return (count($dt) > 0);
	}
}

?>