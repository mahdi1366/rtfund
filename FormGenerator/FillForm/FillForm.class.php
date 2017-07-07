<?php

//-----------------------------
//	Programmer	: Jafarkhani
//	Date		: 94.08
//-----------------------------

class FRG_FillForms extends OperationClass {

    const TableName = "FRG_FillForms";
    const TableKey = "FillFormID";

    public $FillFormID;
    public $FormID;
    public $PersonID;
    public $RegDate;
	
	static function CreateEmptyFillForms($FormID){
		
		$obj = new FRG_FillForms();
		$obj->FormID = $FormID;
		$obj->PersonID = $_SESSION["USER"]["PersonID"];
		$obj->RegDate = PDONOW;
		$obj->Add();
		
		return $obj->FillFormID;
	}
}

class FRG_FillFormElems extends OperationClass {

    const TableName = "FRG_FillFormElems";
    const TableKey = "RowID";

    public $RowID;
    public $FillFormID;
    public $ElementID;
    public $ElementValue;

	static function Get($where = '', $whereParams = array(), $pdo = null) {
		$query = "select * from " . static::TableName . " 
				join FRG_FormElems using(ElementID)
				where 1=1 " . $where;
		return PdoDataAccess::runquery_fetchMode($query, $whereParams, $pdo);
	}

	public static function RemoveAll($FillFormID, $pdo = null) {
		
        return parent::delete(static::TableName, "FillFormID=:FillFormID", 
				array(":FillFormID" => $FillFormID), $pdo);
	}
}
?>
