<?php
//---------------------------
// programmer:	Mahdipour
// create Date:	92.07
//---------------------------

class manage_Job extends PdoDataAccess
{
	public $job_id ;
	public $title;
	public $PersonType;        
        
	function __construct($job_id = "")
	 {
		        
	 	return;
	 }

	 static function GetAll($where = "",$whereParam = array())
	 {
	 	
		$query = " SELECT *
						FROM jobs  " ; 
		
	 	$query .= ($where != "") ? " where " . $where : "";
		/* parent::runquery($query, $whereParam);
		echo PdoDataAccess::GetLatestQueryString() ; die() ;  */ 
				
				
		return parent::runquery($query, $whereParam)  ; 
	 }

        
	function Add()
	{                        
		$return = parent::insert("jobs",$this);

	 	if($return === false)
			return false;

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_add;
		$daObj->MainObjectID = $this->job_id;                
		$daObj->TableName = "jobs";
		$daObj->execute();

		return true;
	}
        
	function Edit() {

		$query = "update jobs set title = '".$this->title."' 
								where job_id= ".$this->job_id ; 
		$result = parent::runquery($query) ; 

		if ($result === false)
			return false;	

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_update;
		$daObj->MainObjectID = $this->job_id;				
		$daObj->TableName = "jobs";
		$daObj->execute();

		return true;
	}	

	static function Remove($jid)
	{
        /*                       
                $res = parent::runquery(" select count(*) cn from person_educations where university_id = ".$uid ." and country_id =".$cid );

                if($res[0]['cn'] > 0 )
                {
                    parent::PushException("حذف این رکورد به علت استفاده در اطلاعات پرسنل امکان پذیر نمی باشد.");
                    return false ;
                }
            */
				$result = parent::delete("jobs","job_id=:JID ",array(":JID" => $jid ));                

				if(!$result)
						return false;

				$daObj = new DataAudit();
				$daObj->ActionType = DataAudit::Action_delete;
				$daObj->MainObjectID = $jid;				
				$daObj->TableName = "jobs";
				$daObj->execute();

				return true;
        }

}

?>