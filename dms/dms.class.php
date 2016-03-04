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
	public $ObjectID2;
	public $IsConfirm;
	public $RegPersonID;
	public $ConfirmPersonID;
	public $RejectDesc;
			
	function __construct($DocumentID = "") {
		
		if($DocumentID != "")
			PdoDataAccess::FillObject ($this, "select * from DMS_documents where DocumentID=?", array($DocumentID));
	}
	
	static function SelectAll($where = "", $param = array()){
		
		return PdoDataAccess::runquery("
			select d.*, b1.infoDesc DocTypeDesc, 
				concat(p1.fname, ' ', p1.lname) confirmfullname,
				concat(p2.fname, ' ', p2.lname) regfullname,
				b2.infoDesc param1Title	,b1.param1,
				if(count(df.RowID) >0,'true','false') HaveFile
			from DMS_documents d	
			left join DMS_DocFiles df using(DocumentID)
			join BaseInfo b1 on(InfoID=d.DocType AND TypeID=8)
			left join  BaseInfo b2 on(b1.param1=b2.InfoID AND b2.TypeID=7)
			left join BSC_persons p1 on(p1.PersonID=d.ConfirmPersonID)
			left join BSC_persons p2 on(p2.PersonID=d.RegPersonID)
			where " . $where . " group by d.DocumentID", $param);
	}
	
	function AddDocument(){
		$this->RegPersonID = $_SESSION["USER"]["PersonID"];
		
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
	
	function EditDocument(){
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

class DMS_DocFiles extends PdoDataAccess
{
	public $RowID;
	public $DocumentID;
	public $PageNo;
	public $FileType;
	public $FileContent;
			
	function __construct($RowID = ""){
		
		if($RowID != "")
			PdoDataAccess::FillObject ($this, "select * from DMS_DocFiles where RowID=?", array($RowID));
	}	
	
	static function SelectAll($where = "", $param = array()){
		
		return PdoDataAccess::runquery("select * from DMS_DocFiles
			where " . $where, $param);
	}
	
	function AddPage(){
		
		$db = PdoDataAccess::getPdoObject();
		$stmt = $db->prepare("insert into DMS_DocFiles(DocumentID,PageNo,FileType,FileContent) 
			values(:did,:p,:ft,:data)");
		
		$stmt->bindParam(":did", $this->DocumentID);
		$stmt->bindParam(":p", $this->PageNo);
		$stmt->bindParam(":ft", $this->FileType);
		$stmt->bindParam(":data", $this->FileContent, PDO::PARAM_LOB);
		$stmt->execute();
		
		$this->RowID = $db->lastInsertId();
		
		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_add;
		$daObj->MainObjectID = $this->RowID;
		$daObj->SubObjectID = $this->DocumentID;		
		$daObj->TableName = "DMS_DocFiles";
		$daObj->execute();
		return true;
	}
	
	static function DeletePage($RowID){
		
		$obj = new DMS_DocFiles($RowID);
		
		if( parent::delete("DMS_DocFiles"," RowID=?", array($RowID)) === false )
	 		return false;

		unlink(getenv("DOCUMENT_ROOT") . "/storage/documents/". $RowID . "." . $obj->FileType);
		
		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_delete;
		$daObj->MainObjectID = $RowID;
		$daObj->TableName = "DMS_DocFiles";
		$daObj->execute();
	 	return true;
	}
}


?>
