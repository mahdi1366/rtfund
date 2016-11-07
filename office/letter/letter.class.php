<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 94.10
//-----------------------------
 
class OFC_letters extends PdoDataAccess{
	
    public $LetterID;
	public $LetterType;
	public $LetterTitle;
	public $LetterDate;
	public $RegDate;
	public $PersonID;
	public $context;
	public $organization;
	public $OrgPost;
	public $SignerPersonID;
	public $SignPostID;
	public $IsSigned;
	public $InnerLetterNo;
	public $InnerLetterDate;
	public $OuterCopies;
	public $RefLetterID;
	public $OuterSendType;
	public $AccessType;
	public $keywords;
	public $PostalAddress;

    function __construct($LetterID = ""){
		$this->DT_LetterDate = DataMember::CreateDMA(DataMember::DT_DATE);
		$this->DT_RegDate = DataMember::CreateDMA(DataMember::DT_DATE);
		$this->DT_InnerLetterDate = DataMember::CreateDMA(DataMember::DT_DATE);
		
		if($LetterID != "")
			parent::FillObject($this, "select * from OFC_letters where LetterID=?", array($LetterID));
    }

    static function GetAll($where = "",$whereParam = array()){
	    $query = "select * from OFC_letters";
	    $query .= ($where != "") ? " where " . $where : "";
	    return parent::runquery($query, $whereParam);
    }
	
	static function FullSelect($where = "",$whereParam = array(), $OrderBy = ""){
		
	    $query = "select l.* , s.SendDate,s.SendComment,
				concat(p1.fname,' ',p1.lname) RegName,
				concat(p2.fname,' ',p2.lname) sender,
				concat(p3.fname,' ',p3.lname) receiver,
				concat(p4.fname,' ',p4.lname) signer
			from OFC_letters l 
			join OFC_send s using(LetterID)
			join BSC_persons p1 on(l.PersonID=p1.PersonID)
			join BSC_persons p2 on(s.FromPersonID=p2.PersonID)
			join BSC_persons p3 on(s.ToPersonID=p3.PersonID)
			left join BSC_persons p4 on(l.SignerPersonID=p4.PersonID)
			left join DMS_documents on(ObjectType='letterAttach' AND ObjectID=s.LetterID)
			left join OFC_LetterCustomers lc on(l.LetterID=lc.LetterID)
		";
	    $query .= ($where != "") ? " where " . $where : "";
		$query .= " group by s.SendID ";
		$query .= $OrderBy;
		
	    return parent::runquery_fetchMode($query, $whereParam);
    }
	
    function AddLetter(){
	    if( parent::insert("OFC_letters", $this) === false )
		    return false;

	    $this->LetterID = parent::InsertID();

	    $daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_add;
		$daObj->MainObjectID = $this->LetterID;
		$daObj->TableName = "OFC_letters";
		$daObj->execute();
		return true;	
    }

    function EditLetter($pdo = null){
	    $whereParams = array();
	    $whereParams[":kid"] = $this->LetterID;

	    if( parent::update("OFC_letters",$this," LetterID=:kid", $whereParams, $pdo) === false )
		    return false;

	    $daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_update;
		$daObj->MainObjectID = $this->LetterID;
		$daObj->TableName = "OFC_letters";
		$daObj->execute($pdo);
		return true;	
    }

    static function RemoveLetter($LetterID){
	    $result = parent::delete("OFC_letters", "LetterID=:kid ",
		    array(":kid" => $LetterID));

	    if($result === false)
		    return false;

	    $daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_delete;
		$daObj->MainObjectID = $LetterID;
		$daObj->TableName = "OFC_letters";
		$daObj->execute();
		return true;	
    }
	
	static function SelectReceivedLetters($where = "", $param = array()){
		 
		$query = "select s.*,l.*, 
				concat_ws(' ',fname, lname,CompanyName) FromPersonName,
				if(count(DocumentID) > 0,'YES','NO') hasAttach,
				substr(s.SendDate,1,10) _SendDate
				
			from OFC_send s
				join OFC_letters l using(LetterID)
				join BSC_persons p on(s.FromPersonID=p.PersonID)
				left join DMS_documents on(ObjectType='letterAttach' AND ObjectID=s.LetterID)
				left join OFC_send s2 on(s2.LetterID=s.LetterID AND s2.SendID>s.SendID AND s2.FromPersonID=s.ToPersonID)
			where s2.SendID is null AND s.ToPersonID=:tpid " . $where . "
			group by SendID";
		$param[":tpid"] = $_SESSION["USER"]["PersonID"];
		
		$query .= dataReader::makeOrder();
		return PdoDataAccess::runquery_fetchMode($query, $param);
	}
	
	static function SelectDraftLetters($where = "", $param = array()){
		
		$query = "select * from OFC_letters
			left join OFC_send using(LetterID) 
			where SendID  is null AND PersonID=:pid " . $where;
		$param[':pid'] = $_SESSION["USER"]["PersonID"];

		$query .= dataReader::makeOrder();
		return PdoDataAccess::runquery($query, $param);
	}
	
	static function SelectSendedLetters($where = "", $param = array()){
		
		$query = "select s.*,l.*, 
				concat_ws(' ',fname, lname,CompanyName) ToPersonName,
				if(count(DocumentID) > 0,'YES','NO') hasAttach,
				substr(s.SendDate,1,10) _SendDate
			from OFC_send s
				join OFC_letters l using(LetterID)
				join BSC_persons p on(s.ToPersonID=p.PersonID)
				left join DMS_documents on(ObjectType='letterAttach' AND ObjectID=s.LetterID)
			where FromPersonID=:fpid " . $where . "
			group by SendID
		";
		$param[":fpid"] = $_SESSION["USER"]["PersonID"];
		$query .= dataReader::makeOrder();

		return PdoDataAccess::runquery_fetchMode($query, $param);
	}
}

class OFC_send extends PdoDataAccess{
	
	public $SendID;
    public $LetterID;
	public $FromPersonID;
	public $ToPersonID;
	public $SendDate;
	public $SendType;
	public $SendComment;
	public $IsUrgent;
	public $IsSeen;
	public $IsDeleted;
	public $IsCopy;
	public $ResponseTimeout;
	public $FollowUpDate;

    function __construct($SendID = ""){
		
		$this->DT_SendDate = DataMember::CreateDMA(DataMember::DT_DATE);
		$this->DT_ResponseTimeout = DataMember::CreateDMA(DataMember::DT_DATE);
		$this->DT_FollowUpDate = DataMember::CreateDMA(DataMember::DT_DATE);
		
		if($SendID != "")
			parent::FillObject($this, "select * from OFC_send where SendID=?", array($SendID));
    }

    static function GetAll($where = "",$whereParam = array()){
	    $query = "select * from OFC_send ";
	    $query .= ($where != "") ? " where " . $where : "";
	    return parent::runquery($query, $whereParam);
    }
	
    function AddSend($pdo = null){
	    if( parent::insert("OFC_send", $this, $pdo) === false )
		    return false;

	    $this->SendID = parent::InsertID($pdo);

	    $daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_add;
		$daObj->MainObjectID = $this->SendID;
		$daObj->SubObjectID = $this->LetterID;
		$daObj->TableName = "OFC_send";
		$daObj->execute($pdo);
		return true;	
    }
	
	function EditSend($pdo = null){
	    if( parent::update("OFC_send", $this, "SendID=:s", array(":s" =>$this->SendID), $pdo) === false )
		    return false;

	    $daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_update;
		$daObj->MainObjectID = $this->SendID;
		$daObj->TableName = "OFC_send";
		$daObj->execute($pdo);
		return true;	
    }
	
	function DeleteSend($pdo = null){
	    if( parent::delete("OFC_send", "SendID=?", array($this->SendID), $pdo) === false )
		    return false;

	    $daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_delete;
		$daObj->MainObjectID = $this->SendID;
		$daObj->TableName = "OFC_send";
		$daObj->execute($pdo);
		return true;	
    }
	
	static function UpdateIsSeen($SendID){
		$obj = new OFC_send($SendID);
		
		if($obj->ToPersonID != $_SESSION["USER"]["PersonID"])
			return false;
		
		$obj->IsSeen = "YES";		
		return $obj->EditSend();
	}
}

class OFC_archive extends PdoDataAccess{
	
	public $FolderID;
    public $ParentID;
	public $FolderName;
	public $PersonID;
	
    function AddFolder($pdo = null){
	    if( parent::insert("OFC_archive", $this, $pdo) === false )
		    return false;

	    $this->FolderID = parent::InsertID($pdo);

	    $daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_add;
		$daObj->MainObjectID = $this->FolderID;
		$daObj->TableName = "OFC_archive";
		$daObj->execute($pdo);
		return true;	
    }
	
	function EditFolder($pdo = null){
	    if( parent::update("OFC_archive", $this, "FolderID=:s", array(":s" =>$this->FolderID), $pdo) === false )
		    return false;

	    $daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_update;
		$daObj->MainObjectID = $this->FolderID;
		$daObj->TableName = "OFC_archive";
		$daObj->execute($pdo);
		return true;	
    }
	
	static function DeleteFolder($FolderID){
		
		PdoDataAccess::runquery("delete from OFC_ArchiveItems where FolderID=?", array($FolderID));
		
	    if( parent::delete("OFC_archive", "FolderID=?", array($FolderID)) === false )
		    return false;

	    $daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_delete;
		$daObj->MainObjectID = $FolderID;
		$daObj->TableName = "OFC_archive";
		$daObj->execute();
		return true;	
    }
	
	static function IsEmpty($FolderID){
		
		$dt = PdoDataAccess::runquery_fetchMode("select * from OFC_ArchiveItems where FolderID=?",
			array($FolderID));
		
		return $dt->rowCount() > 0;
	}
}

class OFC_LetterCustomers extends OperationClass{
	
	const TableName = "OFC_LetterCustomers";
	const TableKey = "RowID";
	
	public $RowID;
	public $LetterID;
	public $PersonID;
	public $IsHide;
	public $LetterTitle;
}

class OFC_templates extends OperationClass{
	
	const TableName = "OFC_templates";
	const TableKey = "TemplateID";
	
	public $TemplateID;
	public $TemplateTitle;
	public $context;
}

class OFC_LetterNotes extends OperationClass{
	
	const TableName = "OFC_LetterNotes";
	const TableKey = "NoteID";
	
	public $NoteID;
	public $LetterID;
	public $PersonID;
	public $NoteTitle;
	public $NoteDesc;
	public $ReminderDate;
	public $IsSeen;
	
	public function __construct($id = '') {
		
		$this->DT_ReminderDate = DataMember::CreateDMA(DataMember::DT_DATE);		
		parent::__construct($id);
	}
	
	public static function GetRemindNotes(){
		
		return self::Get(" AND PersonID=? AND now()>= ReminderDate AND IsSeen='NO'", 
			array($_SESSION["USER"]["PersonID"]));
	}
}
?>
