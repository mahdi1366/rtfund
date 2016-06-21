<?php
//---------------------------
// programmer:	Mahdipour
// create Date:	92.07
//---------------------------

class manage_person_job extends PdoDataAccess
{
	public $PersonID;
	public $FromDate;
	public $ToDate;
	public $JobID;
	public $RowNO;
	

	public function  __construct() {
		
		$this->DT_FromDate = DataMember::CreateDMA(DataMember::DT_DATE);
		$this->DT_ToDate = DataMember::CreateDMA(DataMember::DT_DATE);
	}

	static function GetAllPersonJob($where = "",$whereParam = array())
	{ 
		$query = " select p.* , j.title job_title
						from PersonJobs p inner join jobs j on p.JobID = j.job_id
							where (1=1) ";
		
        
		$query .= ($where != "") ? " AND " . $where : "";
				
		$temp = parent::runquery($query, $whereParam);

		return $temp;
	}
	
	 function AddJobHistory()
	 { 
	 	$this->RowNO  = (manage_person_job::LastID($this->PersonID)+1);
					
	 	if( PdoDataAccess::insert("PersonJobs", $this) === false )
			return false;

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_add;		
		$daObj->RelatedPersonID = $this->PersonID;
		$daObj->MainObjectID = $this->RowNO;
		$daObj->TableName = "PersonJobs";
		$daObj->execute();
		
		return true; 	
	 }
	 
	 function EditJobHistory()
	 { 
	
	 	$whereParams = array();
	 	$whereParams[":pid"] = $this->PersonID;
	 	$whereParams[":rowid"] = $this->RowNO ; 
		
		if( PdoDataAccess::update("PersonJobs",$this," PersonID=:pid and RowNO=:rowid ", $whereParams) === false)
	 		return false;
			
		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_update;
		$daObj->RelatedPersonType = DataAudit::PersonType_staff;
		$daObj->RelatedPersonID = $this->PersonID;
		$daObj->MainObjectID = $this->RowNO;
		$daObj->TableName = "PersonJobs";
		$daObj->execute();

	 	return true;
	
	 }
	 
	static  function RemoveJobHistory($PersonID,$row_no)
	 {
	 	
	 	$whereParams = array();
	 	$whereParams[":pid"] = $PersonID;
	 	$whereParams[":rowid"] = $row_no;
	 	
	 	if( PdoDataAccess::delete("PersonJobs"," PersonID=:pid and RowNO=:rowid", $whereParams) === false) {
			parent::PushException(ER_PERSON_DEP_DEL);
	 		return false;	 	
	 	}

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_delete;		
		$daObj->RelatedPersonID = $PersonID;
		$daObj->MainObjectID = $row_no;
		$daObj->TableName = "PersonJobs";
		$daObj->execute();
	 	
	 	return true;
	 			
	 }
	 
	static function CountPersonJob($where = "",$whereParam = array())
	{
		$query = " select count(*)                               
						from PersonJobs 
								where (1=1) ";
		
		$query .= ($where != "") ? " AND " . $where : "";		
		
		$temp = parent::runquery($query, $whereParam);
			    
		return $temp[0][0];
	}
	
	private static function LastID($PersonID)
	 {
	 	$whereParam = array();
	 	$whereParam[":PD"] = $PersonID;
	 	
	 	return parent::GetLastID("PersonJobs","RowNO","PersonID=:PD",$whereParam);
	 }
		 
	 
}


?>