<?php
//---------------------------
// programmer:	Mahdipour
// create Date:	94.11
//---------------------------

class manage_person_dependency extends PdoDataAccess
{
	public $row_no;
	public $dependency;
	public $fname;
	public $lname;
    public $birth_date;
    public $idcard_no;
    public $idcard_location;
	public $father_name;
	public $marriage_date;
	public $separation_date;
	public $insure_no;
	public $comments;	 
	public $dep_person_id;

	function  __construct()
	{
		$this->DT_birth_date = DataMember::CreateDMA(DataMember::DT_DATE);
		$this->DT_marriage_date = DataMember::CreateDMA(DataMember::DT_DATE);
		$this->DT_separation_date = DataMember::CreateDMA(DataMember::DT_DATE);
	}

	static function GetAllDependency($where="", $whereParam = array())
	{
		$query = " select  pd.*,
                           bi.InfoDesc ,
                           bi.TypeID

                   	from  HRM_person_dependents pd
						INNER JOIN BaseInfo bi ON ( bi.InfoID = pd.dependency  and bi.TypeID = 54 )
						";
		$query .= ($where != "") ? " where " . $where : "";
				
		$temp = parent::runquery($query, $whereParam);
		 
		for($i=0 ; $i<count($temp);$i++)
		{
			$query = "select 
                              bi.InfoDesc     	insure_type
				      from HRM_person_dependent_supports  pds inner join BaseInfo bi on pds.insure_type =  bi.InfoID and bi.TypeID = 55

				      where PersonID = ".$temp[$i]['PersonID']." AND
		        	        master_row_no = ".$temp[$i]['row_no']." AND
		        	        (pds.from_date <='".date('Y-m-d')."' AND
		        	        (pds.to_date >= '".date('Y-m-d')."' OR pds.to_date IS  NULL OR  pds.to_date = '0000-00-00' ) AND
		        	        pds.status IN (1,2))  ";
        
			$tmp = parent::runquery($query);
			   
			if( count($tmp) == 0 )
			    $temp[$i]['insure_type'] ="";
			else      
			    $temp[$i]['insure_type'] = $tmp[0][0];
		}
       
		return $temp;	
		
	}

	function AddDependency()
	{  
					
	 	$this->row_no = (manage_person_dependency::LastID($this->PersonID)+1);
				
	 	if( parent::insert("HRM_person_dependents", $this) === false )
			return false;

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_add;
		$daObj->MainObjectID = $this->row_no;
		$daObj->RelatedPersonType = 5 ;
		$daObj->RelatedPersonID = $this->PersonID;
		$daObj->TableName = "HRM_person_dependents";
		$daObj->execute();

		return true;
		 	
	 }
	 
	function EditDependency()
	{ 
	 	$whereParams = array();
	 	$whereParams[":pid"] = $this->PersonID;
	 	$whereParams[":rowid"] = $this->row_no;
	 	
	 	if( parent::update("HRM_person_dependents",$this," PersonID=:pid and row_no=:rowid", $whereParams) === false )
	 		return false;

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_update;
		$daObj->MainObjectID = $this->row_no;
		$daObj->RelatedPersonType = 5 ;
		$daObj->RelatedPersonID = $this->PersonID;
		$daObj->TableName = "HRM_person_dependents";
		$daObj->execute();
		
	 	return true;
	 }

	static function RemoveDependency($PersonID,$row_no)
	{
	 	$whereParams = array();
	 	$whereParams[":pid"] = $PersonID;
	 	$whereParams[":rowid"] = $row_no;
	 	
	 	if(parent::delete("person_dependents"," PersonID=:pid and row_no=:rowid", $whereParams) === false)
		{
			$error = implode("", parent::popExceptionDescription());
			if(strpos($error, "a foreign key constraint fails") !== false)
			{
				if(strpos($error, "person_dependent_supports") !== false)
					parent::PushException("این وابسته دارای سابقه کفالت بوده و قابل حذف نمی باشد.");
				else
					parent::PushException("از این وابسته در جای دیگری استفاده شده و قابل حدف نمی باشد.");
			}
			else
				parent::PushException($error);
				
	 		return false;	 	
	 	}
	 	
		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_delete;
		$daObj->MainObjectID = $row_no;
		$daObj->RelatedPersonType = DataAudit::PersonType_staff;
		$daObj->RelatedPersonID = $PersonID;
		$daObj->TableName = "person_dependents";
		$daObj->execute();

	 	return true;		
	 }

	static	 function GetAllDepSupports ($where, $whereParam)
	{
		$query = " select DISTINCT  dsh.PersonID,
									c.title,
									s.staff_id,
									concat(p.plname,' ',p.pfname) full_name
				   from person_dependent_supports dsh
											LEFT OUTER JOIN persons p
												ON (dsh.PersonID = p.PersonID)
											LEFT OUTER JOIN staff s
												ON (p.PersonID=s.PersonID)
											LEFT OUTER JOIN writs w
												ON(	s.last_writ_id=w.writ_id AND s.last_writ_ver=w.writ_ver)
											LEFT OUTER JOIN cost_centers c
												ON(w.cost_center_id=c.cost_center_id)

				   where (dsh.status=".IN_EMPLOYEES." OR
						  dsh.status=".DELETE_IN_EMPLOYEES.") AND
						  s.person_type IN (1,2,3)";
      
		$query .= ($where != "") ? " AND " . $where : "";

		
		return parent::runquery($query,$whereParam);

	}

	static function Change_State_Dep($PIDs)
	{		
		$query = " UPDATE person_dependent_supports
 						SET status=".IN_SALARY."
							WHERE status=".IN_EMPLOYEES." AND PersonID in  (" .$PIDs. ")" ;
		if(PdoDataAccess::runquery($query, array()) === false )
		   return false ;

		return true ; 
		
	}

	static function Delete_Dependent_Supports($PIDs)
	{
		$query = " SELECT PersonID,
 							 row_no,
 							 master_row_no,
 							 calc_year_from
					 FROM person_dependent_supports
					 WHERE PersonID IN (".$PIDs.") AND status=".DELETE_IN_EMPLOYEES ;

		$tmp = parent::runquery($query);

		 for($i= 0 ; $i <count($tmp); $i++)
		 {
			if( $tmp[$i]['calc_year_from']!= '0000-00-00' && $tmp[$i]['calc_year_from']!= NULL ) {
				$qry = " update person_dependent_supports
								set status = ".DELETE_IN_SALARY."
										where PersonID = ".$tmp[$i]['PersonID']." and row_no = ".$tmp[$i]['row_no']." and  master_row_no = " . $tmp[$i]['master_row_no'];

				if(PdoDataAccess::runquery($qry, array()) === false )
				   return false ;
				   
			}
			else {
					$qry = " delete from  person_dependent_supports
								where PersonID = ".$tmp[$i]['PersonID']." and row_no = ".$tmp[$i]['row_no']." and  master_row_no = " . $tmp[$i]['master_row_no'];

				if(PdoDataAccess::runquery($qry, array()) === false )
				   return false ;
				
			}
			 
		 }

		 return true ; 
		
	}
	
	static function CountDependency($where = "",$whereParam = array())
	{
		$query = " SELECT count(*) 
		           from  HRM_person_dependents pd
		                 INNER JOIN BaseInfo bi ON ( bi.InfoID = pd.dependency  and bi.TypeID = 54 )
						";
		
		$query .= ($where != "") ? " where " . $where : "";
		
		$temp = parent::runquery($query, $whereParam);
			
		return $temp[0][0];
	}
	
	static  function CountDependencyHistory($where = "",$whereParam = array())
	{
		$query = " SELECT count(*)
				   from  person_dependent_supports dsh
						 LEFT OUTER JOIN person_dependents pd ON ((dsh.PersonID = pd.PersonID) AND (dsh.master_row_no = pd.row_no))
						 LEFT OUTER JOIN persons p ON (pd.PersonID = p.PersonID)
						 
				 ";
		$query .= ($where != "") ? " where " . $where : "";

		$temp = parent::runquery($query, $whereParam);

		return $temp[0][0];
	}
	
	private static function LastID($PersonID)
	{
	 	$whereParam = array();
	 	$whereParam[":PID"] = $PersonID;
	 	
	 	return parent::getLastID("HRM_person_dependents", "row_no", "PersonID=:PID", $whereParam);
	 }

	/**
	 * این تابع تعداد افراد تحت کفالت فرد را بر می گرداند
	 * @param int $PersonID : کد پرسنلی فرد
	 * @param MiladiDate $fromDate
	 * @param MiladiDate $toDate
	 */
	public static function bail_count($PersonID,$Ptype, $fromDate = "", $toDate = "")
	{
		$query = " select COUNT(DISTINCT pd.row_no) count
					from HRM_person_dependent_supports dsh
						LEFT OUTER JOIN HRM_person_dependents pd
							ON ((pd.PersonID = dsh.PersonID) AND (pd.row_no = dsh.master_row_no))";
					
				
		
		
		 if($Ptype == HR_WORKER  )
			{
				$query .= " where pd.PersonID = $PersonID AND 
				 				  dsh.status IN (".IN_EMPLOYEES.",".IN_SALARY.") AND 
	                             (((pd.dependency = ".BOY.") AND (dsh.support_cause = ".UNDERAGE_SUPPORT_CAUSE." OR dsh.support_cause = 12 OR
	                                dsh.support_cause = ".ACQUIREMENT_SUPPORT_CAUSE." OR dsh.support_cause = ".OBSOLETE_SUPPORT_CAUSE.") 
								    /* AND 
									( pd.marriage_date IS NULL OR pd.marriage_date ='0000-00-00'  OR pd.marriage_date > '$fromDate' ) AND
									( pd.separation_date IS NULL OR pd.separation_date ='0000-00-00'  OR pd.separation_date > '$fromDate' )*/
					           ) OR
	                              ((pd.dependency = ".DAUGHTER.") AND ( dsh.support_cause = ".UNDERAGE_SUPPORT_CAUSE." OR dsh.support_cause = 12 OR
	                                dsh.support_cause = ".ACQUIREMENT_SUPPORT_CAUSE." OR dsh.support_cause = ".OBSOLETE_SUPPORT_CAUSE.") AND
									( pd.marriage_date IS NULL OR pd.marriage_date ='0000-00-00'  OR pd.marriage_date > '$fromDate'  ) AND
									( pd.separation_date IS NULL OR pd.separation_date ='0000-00-00' OR pd.separation_date > '$fromDate' ) )) " ;
			
			}
        	
			
		$query .= ($fromDate != "") ? " AND dsh.from_date <= '$fromDate' " : ""; 
		$query .= ($toDate != "") ? " AND ('$toDate' <= dsh.to_date OR dsh.to_date is null OR  dsh.to_date = '0000-00-00' )" : ""; 
        
		$temp = parent::runquery($query);
	
	    if (count($temp) == 0)
	      	return 0;
elseif($fromDate > '2014-03-20') 
		{			
			return $temp[0]['count'] ;
		}
	    else
	    {
			if(($Ptype == HR_CONTRACT || $Ptype == HR_EMPLOYEE )   && $fromDate > '2014-03-20'){
				return $temp[0]['count'] ; 				
			}
			
		if( $Ptype == HR_CONTRACT ){

		    if ($temp[0]['count'] > 2 )
		    return 2 ;
		}
					    
		elseif( $Ptype == HR_PROFESSOR  &&  $fromDate > '2013-09-22') { 
			return $temp[0]['count'] ; 

		}  
		else {
			if ($temp[0]['count'] > 3 )
				return 3 ;
		}
	    	
	    	 return $temp[0]['count']; 
	    }
	}





}



?>