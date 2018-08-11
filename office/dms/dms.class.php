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
			select d.*, b1.infoDesc DocTypeDesc, df.RowID,
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
		
		if(!DMS_DocFiles::DeletePage("", $DocumentID))
		{
			ExceptionHandler::PushException("خطا در حذف صفحات پیوست");
			return false;
		}
		
		if(!PdoDataAccess::delete("DMS_DocParamValues","DocumentID=?", array($DocumentID)))
		{
			ExceptionHandler::PushException("خطا در حذف پارامترهای پیوست");
			return false;
		}
		
		if( parent::delete("DMS_documents"," DocumentID=?", array($DocumentID)) === false )
	 		return false;

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_delete;
		$daObj->MainObjectID = $DocumentID;
		$daObj->TableName = "DMS_documents";
		$daObj->execute();
	 	return true;
	}
	
	static function DeleteAllDocument($ObjectID, $ObjectType){
		
		$dt = PdoDataAccess::runquery("select DocumentID from DMS_documents 
			where ObjectID=? AND ObjectType=?", 
			array($ObjectID, $ObjectType));
		
		foreach($dt as $row)			
			self::DeleteDocument($row["DocumentID"]);

		return ExceptionHandler::GetExceptionCount() == 0;
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
	
	static function DeletePage($RowID = "", $DocumentID = ""){
		
		if($RowID != "")
		{
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
		if($DocumentID != "")
		{
			$dt = PdoDataAccess::runquery("select RowID from DMS_DocFiles where DocumentID=?", 
				array($DocumentID));
			foreach($dt as $row)
				self::DeletePage($row["RowID"]);
			
			return ExceptionHandler::GetExceptionCount() == 0;
		}
	}
}

class DMS_packages extends OperationClass
{
	const TableName = "DMS_packages";
	const TableKey = "PackageID";
	
	public $PackageID;
	public $BranchID;
	public $PackNo;
	public $PersonID;
	
	static function Get($where = '', $whereParams = array(), $pdo = null) {
		
		return parent::runquery_fetchMode("
			select d.* , concat_ws(' ',fname,lname,CompanyName) fullname
			from DMS_packages d
			left join BSC_persons p using(PersonID)
			where 1=1 " . $where, $whereParams, $pdo);		
	}
	
	static function GetPackNo($BranchID){
		
		$dt = parent::runquery("select ifnull(max(PackNo),0)+1 from DMS_packages where BranchID=?",
			array($BranchID));
		
		return $dt[0][0];
	}
	
	function PackNoIsValid(){
		
		$dt = parent::runquery("select PackageID from DMS_packages 
			where BranchID=? AND PackNo=? AND PackageID<>?",
			array($this->BranchID, $this->PackNo, $this->PackageID));
		return count($dt) == 0;		
	}
	
	function Remove($pdo = null) {
		
		if(!parent::delete("DMS_PackageItems", "PackageID=?", array($this->PackageID), $pdo))
			return false;
		
		return parent::Remove($pdo);
	}
}

class DMS_PackageItems extends OperationClass
{
	const TableName = "DMS_PackageItems";
	const TableKey = "RowID";
	
	public $RowID;
	public $PackageID;
	public $ObjectType;
	public $ObjectID;
	
	static function Get($where = '', $whereParams = array(), $order = "") {
		
		return parent::runquery_fetchMode("
			SELECT i.*,InfoDesc ObjectDesc,bf.param1,bf.param2,bf.param3,
				concat_ws(' ',fname,lname,CompanyName) fullname,
				d.DocumentID,d.DocDesc,d.ObjectType,d.IsConfirm, 
				if(count(df.RowID) >0,'true','false') HaveFile	
				
			FROM DMS_PackageItems i
			join BaseInfo bf on(TypeID=11 AND ObjectType=InfoID)

			left join LON_requests	o1 on(i.ObjectType=1 AND i.ObjectID=RequestID)
			left join CNT_contracts	o3 on(i.ObjectType=2 AND i.ObjectID=ContractID)
			left join PLN_plans		o2 on(i.ObjectType=3 AND i.ObjectID=PlanID)
			
			left join BSC_persons p on(	p.PersonID=o1.LoanPersonID or 
										p.PersonID=o2.PersonID or
										p.PersonID=o3.PersonID or p.PersonID=o3.PersonID2)

			left join DMS_documents d on(d.ObjectType=param4 AND i.ObjectID=d.ObjectID)
			left join DMS_DocFiles df using(DocumentID)
			
			where 1=1" . $where . " " . $order . "
			
			group by d.DocumentID
			", $whereParams);
	}
}

class DMS_DocParams extends OperationClass
{
	const TableName = "DMS_DocParams";
	const TableKey = "ParamID";
	
	public $ParamID;
	public $DocType;
	public $ParamDesc;
	public $ParamType;
	public $KeyTitle;
	public $ParamValues;
	public $IsActive;
	
	function Remove($pdo = null) {
		
		$this->IsActive = "NO";
		return $this->Edit();
	}
}

?>
