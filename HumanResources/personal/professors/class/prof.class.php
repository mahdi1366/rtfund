<?php
//---------------------------
// programmer:	Jafarkhani
// create Date:	88.07.12
//---------------------------

require_once DOCUMENT_ROOT . '/personal/persons/class/education.class.php';

class manage_professors
{
	/**
	 * مرتبه علمی استاد را بر می گرداند
	 * @param int $PersonID
	 * @param MiladiDate $date : اگر این تاریخ به تابع فرستاده شود مرتبه علمی استاد تا قبل این تاریخ را برمی گرداند
	 */
	static function get_science_level($PersonID, $ToDate = "")
	{
		$education_level_rec = manage_person_education::GetEducationLevelByDate($PersonID, $ToDate);
	    
	     switch($education_level_rec["max_education_level"])
	     {
	     	case EDUCATION_LEVEL_BS : //مربي اموزشيار
	     		$science_level = 1;
	     		break;
			
	       	case EDUCATION_LEVEL_MS : //مربي
	       		$science_level = 2;
	       		break;
			
	       	case EDUCATION_LEVEL_PHD : //استاديار
	       		$science_level = 3;
	       		break;
	       		
	       	default : 
	       		$science_level = 0;
	    }
	    return $science_level;
	}
	
	/**
	 * این تابع پایه استاد را بر می گرداند
	 * @param miladiDate $ToDate : اگر این پارامتر به تابع فرستاده شود تابع پایه استاد تا قبل این تاریخ را بر می گرداند
	 */
	static function get_base($PersonID, $ToDate = "") 
	{
		$base = 1;
		$education_level_rec = manage_person_education::GetEducationLevelByDate($PersonID, $ToDate);
		
        if ($education_level_rec['university_id'] == 4)
            $base = 3;
        if ($education_level_rec['max_education_level'] == EDUCATION_LEVEL_MS && $education_level_rec['burse'] == 1)
            $base += 1;
        if ($education_level_rec['max_education_level'] == EDUCATION_LEVEL_PHD && $education_level_rec['burse'] == 1)
            $base += 3;
	
		$query ="
			SELECT  PersonID,
		            personel_relation,
		            devotion_type,
		            sum(amount) sum_amount
		
		    FROM 	person_devotions
		
		    WHERE PersonID = $PersonID AND
		          personel_relation = 1 AND
		          devotion_type = 4
		
		    GROUP BY PersonID, personel_relation, devotion_type";
	   	$temp = PdoDataAccess::runquery($query);
		if(count($temp) != 0)
			$base += floor($temp[0]['sum_amount'] / 360);
	
		$query = "
			SELECT  PersonID,
		            military_type
		    FROM 	persons
		    WHERE PersonID = $PersonID";
		$temp = PdoDataAccess::runquery($query);
		if(count($temp) != 0 && $temp[0]['military_type'] == 12)
			$base += 1;
		
		return $base;
		
	}
	
}
?>