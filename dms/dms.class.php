<?php
//---------------------------
// programmer:	Jafarkhani
// create Date: 94.07
//---------------------------

class DMS_documents extends PdoDataAccess
{
	public $DocumentID;
	public $DocDesc;
	public $DocType;
	public $ObjectType;
	public $ObjectID;
	public $FileType;
	public $FileContent;
	public $IsConfirm;
	public $ConfirmPersonID;
			
	function __construct($DocumentID = "") {
		
		if($DocumentID != "")
			PdoDataAccess::FillObject ($this, "select * from DMS_documents where DocumentID=?", array($DocumentID));
	}
	
	static function SelectAll($where = "", $param = array()){
		
		return PdoDataAccess::runquery("
			select d.*, infoDesc DocTypeDesc, concat(fname, ' ', lname) confirmfullname
			from DMS_documents d	
			join BaseInfo on(InfoID=d.DocType AND TypeID=8)
			left join BSC_persons on(PersonID=ConfirmPersonID)
			where " . $where, $param);
	}
	
	function AddDocument()
	{
	 	if(!parent::insert("DMS_documents",$this))
			return false;
		$this->DocumentID = parent::InsertID();
		
		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_add;
		$daObj->MainObjectID = $this->DocumentID;
		$daObj->TableName = "DMS_documents";
		$daObj->execute();
		return true;
	}
	
	function EditDocument()
	{
	 	if( parent::update("DMS_documents",$this," DocumentID=:l", array(":l" => $this->DocumentID)) === false )
	 		return false;

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_update;
		$daObj->MainObjectID = $this->DocumentID;
		$daObj->TableName = "DMS_documents";
		$daObj->execute();
	 	return true;
    }
	
	static function DeleteDocument($DocumentID){
		
		if( parent::delete("DMS_documents"," DocumentID=?", array($DocumentID)) === false )
	 		return false;

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_delete;
		$daObj->MainObjectID = $DocumentID;
		$daObj->TableName = "DMS_documents";
		$daObj->execute();
	 	return true;
	}
}

?>
