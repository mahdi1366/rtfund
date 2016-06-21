<?php
//---------------------------
// programmer:	Mahdipour
// create Date:	88.06.17
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
			
	 function OnBeforeInsert()
	 {

		if( $this->duration_year == 0  && $this->duration_month == 0 && $this->duration_day == 0 )
		{
			$duration = DateModules::getDateDiff($this->to_date , $this->from_date );	
										
			$this->group_duration_year = $this->retired_duration_year = $this->duration_year  = floor($duration / 360);
			$this->group_duration_month  = $this->retired_duration_month = $this->duration_month = floor(($duration - ($this->duration_year * 360)) / 30);
			$this->group_duration_day = $this->retired_duration_day = $this->duration_day  = $duration - ($this->duration_year * 360) - ($this->duration_month * 30);

		}

		if ( $this->emp_state == EMP_STATE_NONE_GOVERNMENT) {
    		 $this->retired_duration_year  = 0;
    		 $this->retired_duration_month = 0;
    		 $this->retired_duration_day   = 0;
    	}

    	return true;
		
	 }
	 
	 function AddEmp()
	 {
		
	 	$this->row_no = (manage_person_employment::LastID($this->PersonID)+1);	
		$this->OnBeforeInsert();
	 	if( PdoDataAccess::insert("person_employments", $this) === false )
			return false;

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_add;
		$daObj->RelatedPersonType = DataAudit::PersonType_staff;
		$daObj->RelatedPersonID = $this->PersonID;
		$daObj->MainObjectID = $this->row_no;
		$daObj->TableName = "person_employments";
		$daObj->execute();
		
		return true;	 	
	 }
		 
	 function EditEmp()
	 {
	 	$this->OnBeforeInsert();
		
	 	$whereParams = array();
	 	$whereParams[":pid"] = $this->PersonID;
	 	$whereParams[":rowid"] = $this->row_no;
	 	
	 	if( PdoDataAccess::update("person_employments",$this," PersonID=:pid and row_no=:rowid ", $whereParams) === false )
	 		return false;

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_update;
		$daObj->RelatedPersonType = DataAudit::PersonType_staff;
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
	 
		if( PdoDataAccess::delete("person_employments"," PersonID=:pid and row_no=:rowid", $whereParams) === false) {
			parent::PushException(ER_PERSON_DEP_DEL);
	 		return false;	 	
	 	}

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_delete;
		$daObj->RelatedPersonType = DataAudit::PersonType_staff;
		$daObj->RelatedPersonID = $PersonID;
		$daObj->MainObjectID = $row_no;
		$daObj->TableName = "person_employments";
		$daObj->execute();
		
	 	return true;		
	 }
	 
	static function CountEmp($where = "",$whereParam = array())
	{
		$query = " select count(*)          
                   from person_employments eh
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
                            eh.comments , 
                            BI1.Title org_title ,
                            BI2.Title unempTitle,
							BI3.Title empstateTitle
                                 
                    from    person_employments eh   
						left join  Basic_Info BI1 ON eh.org_type = BI1.InfoID and BI1.TypeID = 32
						left join  Basic_Info BI2 ON eh.unemp_cause = BI2.InfoID and BI2.TypeID = 33
						left join  Basic_Info BI3 ON eh.emp_state = BI3.InfoID and BI3.TypeID = 3
                    where (1=1)";
		
		$query .= ($where != "") ? " AND " . $where : "";
		
		$temp = parent::runquery($query, $whereParam);

		return $temp;
		
	}
	private static function LastID($PersonID)
	 {
	 	$whereParam = array();
	 	$whereParam[":PD"] = $PersonID;
	 	return PdoDataAccess::GetLastID("person_employments","row_no","PersonID=:PD",$whereParam);
	 }
	 		
}


?>