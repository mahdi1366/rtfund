<?php
//---------------------------
// programmer:	Mahdipour
// create Date:	94.11
//---------------------------

class manage_person_education extends PdoDataAccess
{
	 public $PersonID;
	 public $row_no;
	 public $education_level;
	 public $sfid;
	 public $sbid;
	 public $doc_date;
	 public $country_id;
	 public $university_id;
	 public $grade;
	 public $thesis_ptitle;
	 public $thesis_etitle;
	 public $burse;
	 public $certificated;
	 public $comments;
     	 
	function AddEducation()
	{
	 	$this->row_no = (manage_person_education::LastID($this->PersonID)+1);	
		
	 	if( parent::insert("HRM_person_educations", $this) === false ){
		print_r(ExceptionHandler::PopAllExceptions()); 
		die();
			return false;
		}

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_add;
		$daObj->RelatedPersonType = 3;
		$daObj->RelatedPersonID = $this->PersonID;
		$daObj->MainObjectID = $this->row_no;
		$daObj->TableName = "HRM_person_educations";
		$daObj->execute();

		return true;	

	}
	 
	function EditEducation()
	{ 
	 	$whereParams = array();
	 	$whereParams[":pid"] = $this->PersonID;
	 	$whereParams[":rowid"] = $this->row_no;
	 	
	 	if( parent::update("HRM_person_educations",$this," PersonID=:pid and row_no=:rowid ", $whereParams) === false )
	 		return false;
	 
		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_update;
		$daObj->RelatedPersonType = 3 ;
		$daObj->RelatedPersonID = $this->PersonID;
		$daObj->MainObjectID = $this->row_no;
		$daObj->TableName = "person_educations";
		$daObj->execute();
		
	 	return true;
    }
	static function GetAllEducations($where = "",$whereParam = array())
	{ 
		$query = " select pe.PersonID,
                          pe.row_no,  
                          pe.sfid sf_ptitle,
                          '' sb_ptitle,
                          pe.university_id  u_ptitle,
                          c.ptitle c_ptitle,
                          pe.doc_date,
                          pe.grade,
						  pe.comments,
                          bi1.InfoDesc education_level_title ,
                          bi2.InfoDesc burse_title ,
                          pe.education_level ,
                          pe.burse  ,
                          pe.sfid ,
                          pe.sbid ,
                          pe.country_id ,
                          pe.university_id ,
                          pe.certificated , 
                          pe.thesis_ptitle ,
                          pe.thesis_etitle
                     
		           from HRM_person_educations  pe
                            /* LEFT OUTER JOIN HRM_study_branchs sb ON((pe.sbid = sb.sbid) AND (pe.sfid = sb.sfid))
                             LEFT OUTER JOIN HRM_study_fields sf ON(pe.sfid = sf.sfid)
                             LEFT OUTER JOIN HRM_universities u ON((pe.university_id = u.university_id) AND (pe.country_id = u.country_id))*/
                             LEFT OUTER JOIN HRM_countries c ON(pe.country_id = c.country_id)
                             LEFT JOIN BaseInfo bi1 ON(bi1.InfoID = pe.education_level AND bi1.TypeID = 56 )
                             LEFT JOIN BaseInfo bi2 ON(bi2.InfoID = pe.burse AND bi2.TypeID = 57)

					where 1=1"
		;
		$query .= ($where != "") ? " AND " . $where : "";
		
		$temp = parent::runquery($query, $whereParam);

		return $temp;
	}
	 		
	static function CountEducation($where = "",$whereParam = array())
	{
		$query = " select count(*) from HRM_person_educations pe";

		$query .= ($where != "") ? " where " . $where : "";
		$temp = parent::runquery($query, $whereParam);
		return $temp[0][0];
	}
	
	private static function LastID($PersonID)
	 {
	 	$whereParam = array();
	 	$whereParam[":PD"] = $PersonID;
	 	
	 	return parent::GetLastID("HRM_person_educations","row_no","PersonID=:PD",$whereParam);
	 }
	 
	static function RemoveEducation($personID, $row_no)
	{
	 	$whereParams = array();
	 	$whereParams[":pid"] = $personID;
	 	$whereParams[":rowid"] = $row_no;
	 	 	
		 if( parent::delete("HRM_person_educations"," PersonID=:pid and row_no=:rowid", $whereParams) === false) {
			parent::PushException(ER_PERSON_DEP_DEL);
	 		return false;	 	
	 	}
	 	
		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_delete;
		$daObj->RelatedPersonType = 3 ;
		$daObj->RelatedPersonID = $personID;
		$daObj->MainObjectID = $row_no;
		$daObj->TableName = "person_educations";
		$daObj->execute();

	 	return true;
	 		 	
	 }
	 
	static function GetEducationalGroupLevel($educ_level , $selectfield)
	{
		 $query = "select $selectfield  from Basic_Info
						where TypeID = 6 AND InfoID = :elevel ";
		 $temp = parent::runquery($query, array(":elevel" => $educ_level));

		 return $temp[0][$selectfield];

     }

	/**
	  این تابع آخرین وضعیت تحصیلی فرد را تا تاریخ ورودی بر می گرداند
	  * *اگر رکورد آخرین سطح تحصیلی را می خواهید باید پارامتر تاریخ را ارسال نکنید
	  *
	  * @param int $PersonID
	  * @param miladi date $date
	  * @return record
	  */
	static function GetEducationLevelByDate($PersonID, $date = "",$is_auto = "")
	{
		$where = "";
		$whereParam = array();
		$whereParam[":pid"] = $PersonID;
		 
		if($date != "")
		{
			$where = ($date != "") ? " AND doc_date <= :date" : "";
			$whereParam[":date"] = $date;
		}

	   	$query = "select education_level as max_education_level,
	   					row_no,
	   					PersonID,
	   					sfid,
	   					sbid,
						doc_date,
	   					u.university_id,
	   					burse,
						c.ptitle as countryTitle,
						u.ptitle as universityTitle
	   			from HRM_person_educations pe
					LEFT OUTER JOIN HRM_countries c ON(pe.country_id = c.country_id)
				    LEFT OUTER JOIN HRM_universities u ON(pe.university_id = u.university_id AND pe.country_id = u.country_id)
	   			where PersonID = :pid $where
	   			order by doc_date DESC
	   			limit 1";

	   	$temp = parent::runquery($query, $whereParam);
	    if((count($temp) == 0 || $temp[0]["max_education_level"] == "" ) && $is_auto )
	    {
	    	parent::PushException(ERROR_PERSON_EDUCATIONS_NOT_SET);
	        return false;
	    }
        else if(count($temp) == 0 || $temp[0]["max_education_level"] == "" )
        {
            return 101 ;
        }

	    return $temp[0];
	 }
}


?>
