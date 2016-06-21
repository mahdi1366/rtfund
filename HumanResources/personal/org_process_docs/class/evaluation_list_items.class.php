<?php
//---------------------------
// programmer:	Mahdipour
// create Date:	91.01.22
//---------------------------
require_once inc_manage_unit ;
class manage_evaluation_list_items extends PdoDataAccess
{
	public $ListItemID ;
    public $list_id ;
    public $staff_id;
    public $ProprietaryScore;
    public $PublicScore;
    public $social_behaviour_score;
    public $annual_coef;
    public $high_job_coef;
    public $comments;
    public $scores_sum;


	function __construct($salary_item_type_id = "")
	 {
		
	 	return;
	 }

	 static function SelectListOfPrn( $ouid , $person_type)
	 {

	    $whereParams = array();
	    $whereParams[":pt"] = $person_type;
	    $whereParams[":ouid"] = $ouid;
	    $whereParams[":ouid2"] = "%," . $ouid . ",%";
	    $whereParams[":ouid3"] =  $ouid . ",";
	    $whereParams[":ouid4"] = "," . $ouid ;
      
        
        $query = " select s.staff_id from staff s inner join writs w
                                                on s.last_writ_id = w.writ_id and s.last_writ_ver = w.writ_ver and s.staff_id = w.staff_id
                                                  inner join org_new_units ou on w.ouid = ou.ouid
                                                    where s.person_type = :pt AND w.emp_mode not in (  ".EMP_MODE_LEAVE_WITHOUT_SALARY.",".EMP_MODE_CONVEYANCE.",
                                                                                                       ".EMP_MODE_TEMPORARY_BREAK.",".EMP_MODE_PERMANENT_BREAK.",
                                                                                                       ".EMP_MODE_BREAKAWAY.','.EMP_MODE_RUSTICATION.",
                                                                                                       ".EMP_MODE_RETIRE.','.EMP_MODE_RE_BUY.",
                                                                                                       ".EMP_MODE_WITHOUT_SALARY." ) AND 
                         (     ou.parent_path like :ouid2 OR
							    substr(ou.parent_path,1,length(ou.ouid)) = :ouid3  OR
                               substr(ou.parent_path,-length(ou.ouid),length(ou.ouid)) = :ouid4 OR
							   ou.ouid=:ouid OR
							   ou.parent_ouid = :ouid
                               )" ;
         
        $tmp = parent::runquery($query,$whereParams);
	
	return $tmp ;
	
	 }

     function Add()
     {
       
	 	$return = parent::insert("evaluation_list_items",$this);
        
	 	if($return === false)
			return false;

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_add;
		$daObj->MainObjectID = $this->list_id;
        $daObj->SubObjectID = $this->staff_id;
		$daObj->TableName = "evaluation_list_items";
		$daObj->execute();

		return true;
         
     }


     function Edit()
	{
	 	$whereParams = array();
	 	$whereParams[":lid"] = $this->list_id;
        $whereParams[":sid"] = $this->staff_id;
        $whereParams[":lit"] = $this->ListItemID;

	 	$result = parent::update("evaluation_list_items",$this," list_id=:lid and staff_id =:sid and ListItemID =:lit ", $whereParams);

        if(!$result)
			return false;

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_update;
		$daObj->MainObjectID = $this->list_id;
        $daObj->SubObjectID = $this->staff_id ;
		$daObj->TableName = "evaluation_list_items";
		$daObj->execute();

		return true;
	}


    function Remove($All="")
	{

        $query = " select * from evaluation_lists where list_id = ".$this->list_id ." and doc_state = 3 " ;
        
        $tmp2 = parent::runquery($query);

        if (count($tmp2) >  0)
        {
             parent::PushException("این لیست تایید واحد مرکزی می باشد .");
             return false ;
        }
        else
        {
	    if($All=="true")
		$result = parent::delete("evaluation_list_items","list_id=?", array($this->list_id));
		
	    else 
		$result = parent::delete("evaluation_list_items","list_id =? and ListItemID=? ", array($this->list_id,$this->ListItemID));
		
	    
        }

		if(!$result)
			return false;

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_delete;
		$daObj->MainObjectID = $this->list_id;
		$daObj->SubObjectID = $this->ListItemID;
		$daObj->TableName = "evaluation_list_items";
		$daObj->execute();

		return true;
	}     

    
}

?>