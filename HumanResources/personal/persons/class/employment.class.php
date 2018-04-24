<?php
//---------------------------
// programmer:	Mahdipour
// create Date:	96.06
//---------------------------

class manage_person_employment extends PdoDataAccess
{
     
	 public $PersonID;
	 public $row_no;
	 public $from_date;
	 public $to_date;
	 public $organization;
     public $unit;
     public $org_type;
     public $person_type;
	 public $emp_state;
	 public $emp_mode;
	 public $title;
	 public $unemp_cause;
	 public $duration_year;
	 public $duration_month;	 
	 public $duration_day;
	 public $retired_duration_year;
	 public $retired_duration_month;
	 public $retired_duration_day;
	 public $group_duration_year;
	 public $group_duration_month;
	 public $group_duration_day;
	 public $comments;
		
		 
	 function AddEmp()
	 {
		
	 	$this->row_no = (manage_person_employment::LastID($this->PersonID)+1);	
		
	 	if( PdoDataAccess::insert("HRM_person_employments", $this) === false )
			return false;

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_add;	
		$daObj->RelatedPersonID = $this->PersonID;
		$daObj->MainObjectID = $this->row_no;
		$daObj->TableName = "person_employments";
		$daObj->execute();
		
		return true;	 	
	 }
		 
	 function EditEmp()
	 {
	 	
	 	$whereParams = array();
	 	$whereParams[":pid"] = $this->PersonID;
	 	$whereParams[":rowid"] = $this->row_no;
	 	
	 	if( PdoDataAccess::update("HRM_person_employments",$this," PersonID=:pid and row_no=:rowid ", $whereParams) === false )
	 		return false;

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_update;		
		$daObj->RelatedPersonID = $this->PersonID;
		$daObj->MainObjectID = $this->row_no;
		$daObj->TableName = "person_employments";
		$daObj->execute();
		
	 	return true;
	 		
	 }
	 
	static  function RemoveEmp($PersonID,$row_no)
	 {
	 	$whereParams = array();
	 	$whereParams[":pid"] = $PersonID;
	 	$whereParams[":rowid"] = $row_no;
	 
		if( PdoDataAccess::delete("HRM_person_employments"," PersonID=:pid and row_no=:rowid", $whereParams) === false) {
			parent::PushException(ER_PERSON_DEP_DEL);
	 		return false;	 	
	 	}

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_delete;	
		$daObj->RelatedPersonID = $PersonID;
		$daObj->MainObjectID = $row_no;
		$daObj->TableName = "person_employments";
		$daObj->execute();
		
	 	return true;		
	 }
	 
	static function CountEmp($where = "",$whereParam = array())
	{
		$query = " select count(*)          
                   from HRM_person_employments eh
       			   where (1=1)";
		
		$query .= ($where != "") ? " AND " . $where : "";		
		
		$temp = parent::runquery($query, $whereParam);
			    
		return $temp[0][0];
	}
	
	static function GetAllEmp($where = "",$whereParam = array())
	{
							
		$query = " select   eh.PersonID, 
		                    eh.row_no , 
		                    eh.from_date , 
		                    eh.to_date,
							eh.organization,
							eh.unit,
							eh.org_type ,
							eh.person_type ,
							eh.emp_state,
							eh.emp_mode ,
							eh.title , 
							eh.unemp_cause ,
							eh.duration_year,
							eh.duration_month, 
							eh.duration_day,
							eh.retired_duration_year,
							eh.retired_duration_month,
							eh.group_duration_year,
							eh.retired_duration_day ,
							eh.group_duration_month,
							eh.group_duration_day,
                            eh.comments 
                            
                                 
                    from    HRM_person_employments eh   
						
                    where (1=1)";
		
		$query .= ($where != "") ? " AND " . $where : "";
		
		$temp = parent::runquery($query, $whereParam);

		return $temp;
		
	}
	private static function LastID($PersonID)
	 {
	 	$whereParam = array();
	 	$whereParam[":PD"] = $PersonID;
	 	return PdoDataAccess::GetLastID("HRM_person_employments","row_no","PersonID=:PD",$whereParam);
	 }
	 		
}


?>