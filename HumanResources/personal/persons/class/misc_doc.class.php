<?php
//---------------------------
// programmer:	Mahdipour
// create Date:	88.06.17
//---------------------------

class manage_person_misc_doc extends PdoDataAccess
{
	public $PersonID;
	public $row_no;
	public $doc_no;
	public $doc_date;
	public $title;
	public $attachments; 
	public $comments;
     	 
	 function AddMiscDoc()
	 {
	 	$this->row_no = (manage_person_misc_doc::LastID($this->PersonID)+1);	
	 	
	 	if( PdoDataAccess::insert("person_misc_docs", $this) === false )
			return false;
		
		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_add;
		$daObj->RelatedPersonType = DataAudit::PersonType_staff;
		$daObj->RelatedPersonID = $this->PersonID;
		$daObj->MainObjectID = $this->row_no;
		$daObj->TableName = "person_misc_docs";
		$daObj->execute();

		return true;	
		
	 }
	 
	 function EditMiscDoc()
	 { 
	 	$whereParams = array();
	 	$whereParams[":pid"] = $this->PersonID;
	 	$whereParams[":rowid"] = $this->row_no;
	 	
	 	if( PdoDataAccess::update("person_misc_docs",$this," PersonID=:pid and row_no=:rowid ", $whereParams) === false )
	 		return false;

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_update;
		$daObj->RelatedPersonType = DataAudit::PersonType_staff;
		$daObj->RelatedPersonID = $this->PersonID;
		$daObj->MainObjectID = $this->row_no;
		$daObj->TableName = "person_misc_docs";
		$daObj->execute();

	 	return true;
	  		
	 }
	 
	static function GetAllMiscDoc($where = "",$whereParam = array())
	{ 
		$query = " select * from person_misc_docs";
		$query .= ($where != "") ? " where " . $where : "";
		
		$temp = parent::runquery($query, $whereParam);
		return $temp;
	}
	 		
	static function CountMiscDoc($where = "",$whereParam = array())
	{
		$query = " select count(*) from person_misc_docs ";
		$query .= ($where != "") ? " where " . $where : "";		
		
		$temp = parent::runquery($query, $whereParam);
		return $temp[0][0];
	}
	
	private static function LastID($PersonID)
	 {
	 	$whereParam = array();
	 	$whereParam[":PD"] = $PersonID;
	 	
	 	return PdoDataAccess::GetLastID("person_misc_docs","row_no","PersonID=:PD",$whereParam);
	 }
	 
	 static function RemoveMiscDoc($personID, $row_no)
	 {
	 	$whereParams = array();
	 	$whereParams[":pid"] = $personID;
	 	$whereParams[":rowid"] = $row_no;
	 	
		if( PdoDataAccess::delete("person_misc_docs"," PersonID=:pid and row_no=:rowid", $whereParams) === false) {
			parent::PushException(ER_PERSON_DEP_DEL);
	 		return false;	 	
	 	}

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_delete;
		$daObj->RelatedPersonType = DataAudit::PersonType_staff;
		$daObj->RelatedPersonID = $personID;
		$daObj->MainObjectID = $row_no;
		$daObj->TableName = "person_misc_docs";
		$daObj->execute();

	 	return true; 	
	 	
	 }
}



?>