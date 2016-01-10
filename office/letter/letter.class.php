<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 94.10
//-----------------------------

class OFC_letters extends PdoDataAccess{
	
    public $LetterID;
	public $LetterType;
	public $LetterTitle;
	public $SubjectID;
	public $LetterDate;
	public $RegDate;
	public $PersonID;
	public $summary;
	public $context;
	public $LetterStatus;

    function __construct($LetterID = "")
    {
		$this->DT_LetterDate = DataMember::CreateDMA(DataMember::DT_DATE);
		$this->DT_RegDate = DataMember::CreateDMA(DataMember::DT_DATE);
		
		if($LetterID != "")
			parent::FillObject($this, "select * from OFC_letters where LetterID=?", array($LetterID));
    }

    static function GetAll($where = "",$whereParam = array())
    {
	    $query = "select * from OFC_letters";
	    $query .= ($where != "") ? " where " . $where : "";
	    return parent::runquery($query, $whereParam);
    }
	
    function AddLetter()
    {
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

    function EditLetter($pdo = null)
    {
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

    static function RemoveLetter($LetterID)
    {
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

    function __construct($SendID = "")
    {
		$this->DT_SendDate = DataMember::CreateDMA(DataMember::DT_DATE);
		
		if($SendID != "")
			parent::FillObject($this, "select * from OFC_send where SendID=?", array($SendID));
    }

    static function GetAll($where = "",$whereParam = array())
    {
	    $query = "select * from OFC_send ";
	    $query .= ($where != "") ? " where " . $where : "";
	    return parent::runquery($query, $whereParam);
    }
	
    function AddSend($pdo = null)
    {
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
}

?>