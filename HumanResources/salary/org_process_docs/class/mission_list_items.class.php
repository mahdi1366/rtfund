<?php
//---------------------------
// programmer:	Mahdipour
// create Date:	91.06
//---------------------------

class manage_mission_list_items extends PdoDataAccess
{
	
	public $list_id ;
	public $list_row_no ;
	public $doc_no ;
	public $doc_date ;
        public $staff_id ;
        public $from_date ;  
        public $to_date ;  
        public $duration ;  
        public $travel_cost ;  
        public $destination ;  
        public $using_facilities ;         
        public $comments ;
        public $region_coef ; 
        public $salary_item_type_id ; 

	function __construct()
	{		    
                $this->DT_doc_date = DataMember::CreateDMA(DataMember::DT_DATE);
                $this->DT_from_date = DataMember::CreateDMA(DataMember::DT_DATE);
                $this->DT_to_date = DataMember::CreateDMA(DataMember::DT_DATE);
	}
                
        function Add()
	{ 
	    $masterID = parent::GetLastID("mission_list_items", "list_row_no", "list_id=".$this->list_id )  ; 
	    $this->list_row_no = $masterID + 1 ; 
	 
	    $query = " select person_type from staff 
	                     where staff_id = ".$this->staff_id ; 
	    $res = parent::runquery($query) ; 	    
     
	    if( $res[0]['person_type'] == HR_PROFESSOR ) 
		$this->salary_item_type_id = 42 ; 
	    else if( $res[0]['person_type'] == HR_EMPLOYEE ) 
		$this->salary_item_type_id = 43 ;
	    else if( $res[0]['person_type'] == HR_CONTRACT ) 
		$this->salary_item_type_id = 643 ;     
	    
	    $result = parent::insert("mission_list_items", $this);  
	   
	    if($result === false)
	    {
		    return false;
	    }

	    $daObj = new DataAudit();
	    $daObj->ActionType = DataAudit::Action_add;		
	    $daObj->MainObjectID = $this->list_id;
	    $daObj->SubObjectID = $this->list_row_no ; 
	    $daObj->TableName = "mission_list_items";
	    $daObj->execute();   		            

	    return true;		
		
	}
	
	static function GetAllMembers($where)
	 {
	 	$query = " select mli.* , p.pfname , p.plname  
		                    from mission_list_items mli inner join staff s
									on mli.staff_id = s.staff_id 
								 inner join persons p on s.personid = p.personid 
								 ".$where ; 
					
	 	return parent::runquery($query) ;
	 }
	 
	 function Edit()
	{
				 
	    $whereParams = array();
	    $whereParams[":lid"] = $this->list_id;	   
	    $whereParams[":lrn"] = $this->list_row_no ; 
	    $whereParams[":sid"] = $this->staff_id ; 
	     
	    $result = parent::update("mission_list_items",$this," list_id=:lid and staff_id =:sid and list_row_no =:lrn ", $whereParams);

	    if(!$result)
			return false;

	    $daObj = new DataAudit();
	    $daObj->ActionType = DataAudit::Action_update;
	    $daObj->MainObjectID = $this->list_id;
	    $daObj->SubObjectID = $this->list_row_no ;
	    $daObj->TableName = "mission_list_items";
	    $daObj->execute();

	    return true;
	}
	
	function Remove($All="")
	{

	    $query = " select * from pay_get_lists where list_id = ".$this->list_id ." and doc_state = 3 " ;
        
	    $tmp2 = parent::runquery($query);

	    if (count($tmp2) >  0)
	    {
		parent::PushException("این لیست تایید واحد مرکزی می باشد .");
		return false ;
	    }
	    else
	    {
		if($All=="true") {

		$result = parent::delete("mission_list_items","list_id=?", array($this->list_id));
}
		else {
		    
		    $result = parent::delete("mission_list_items","list_id=? and list_row_no =?",array($this->list_id,$this->list_row_no));
			}

	    }

		if(!$result)
			return false;

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_delete;
		$daObj->MainObjectID = $this->list_id;
		$daObj->SubObjectID = $this->list_row_no;
		$daObj->TableName = "mission_list_items";
		$daObj->execute();

		return true;
	}     
	 
}

?>