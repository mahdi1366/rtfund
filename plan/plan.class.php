<?php
//---------------------------
// programmer:	Jafarkhani
// create Date: 94.06
//---------------------------

class PLN_plans extends PdoDataAccess
{
	public $PlanID;
	public $PersonID;
	public $ReqDate;
	public $StatusID;
	
	function __construct($PlanID = "") {
		
		if($PlanID != "")
			PdoDataAccess::FillObject ($this, "select * from PLN_plans where PlanID=?", array($PlanID));
	}
	
	static function SelectAll($where = "", $param = array()){
		
		return PdoDataAccess::runquery("select * from PLN_plans where " . $where, $param);
	}
	
	function AddPlan($pdo = null){
		
		$this->ReqDate = PDONOW;
		
	 	if(!parent::insert("PLN_plans",$this, $pdo))
			return false;
		$this->PlanID = parent::InsertID($pdo);
		
		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_add;
		$daObj->MainObjectID = $this->PlanID;
		$daObj->TableName = "PLN_plans";
		$daObj->execute($pdo);
		return true;
	}
	
	function EditPlan($pdo = null){
		
	 	if( parent::update("PLN_plans",$this," PlanID=:l", array(":l" => $this->PlanID), $pdo) === false )
	 		return false;

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_update;
		$daObj->MainObjectID = $this->PlanID;
		$daObj->TableName = "PLN_plans";
		$daObj->execute($pdo);
	 	return true;
    }
	
	static function DeletePlan($PlanID){
		
		$obj = new LON_requests($PlanID);
		if($obj->StatusID != "1")
			return false;
		
		if( parent::delete("PLN_PlanItems"," PlanID=?", array($PlanID)) === false )
	 		return false;
		
		if( parent::delete("PLN_plans"," PlanID=?", array($PlanID)) === false )
	 		return false;

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_delete;
		$daObj->MainObjectID = $PlanID;
		$daObj->TableName = "PLN_plans";
		$daObj->execute();
	 	return true;
	}
}

class PLN_PlanItems extends PdoDataAccess
{
	public $RowID;
	public $PlanID;
	public $ElementID;
	public $ElementValue;
	
	function __construct($RowID = "") {
		
		if($RowID != "")
			PdoDataAccess::FillObject ($this, "select * from PLN_PlanItems where RowID=?", array($RowID));
	}
	
	static function SelectAll($where = "", $param = array()){
		
		return PdoDataAccess::runquery("select * from PLN_PlanItems	where " . $where, $param);
	}
	
	function AddItem($pdo = null){
		
		if (!parent::insert("PLN_PlanItems", $this, $pdo)) {
			return false;
		}
		$this->RowID = parent::InsertID($pdo);
		
		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_add;
		$daObj->MainObjectID = $this->RowID;
		$daObj->TableName = "PLN_PlanItems";
		$daObj->execute($pdo);
		return true;
	}
	
	function EditItem($pdo = null){
		
	 	if( parent::update("PLN_PlanItems",$this," RowID=:l", array(":l" => $this->RowID), $pdo) === false )
	 		return false;

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_update;
		$daObj->MainObjectID = $this->RowID;
		$daObj->TableName = "PLN_PlanItems";
		$daObj->execute($pdo);
	 	return true;
    }
	
	static function DeleteItem($RowID){
		
		if( parent::delete("PLN_PlanItems"," RowID=?", array($RowID)) === false )
	 		return false;

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_delete;
		$daObj->MainObjectID = $RowID;
		$daObj->TableName = "PLN_PlanItems";
		$daObj->execute();
	 	return true;
	}
}

?>
