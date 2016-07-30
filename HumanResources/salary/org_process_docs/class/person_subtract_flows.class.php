<?php
//---------------------------
// programmer:	Mahdipour
// create Date:	92.08
//---------------------------

class manage_person_subtract_flows extends PdoDataAccess
{
	
	public $subtract_id ;
	public $row_no ;	
	public $flow_type ;
	public $flow_date ;
	public $flow_time ; 
	public $old_remainder ;
	public $new_remainder ;
	public $old_instalment ;
	public $new_instalment ;
	public $comments ;
	

	function __construct()
	{		    
                $this->DT_flow_date = DataMember::CreateDMA(DataMember::DT_DATE);
	}
	
	function AddSubFlow($DB)
	{ 	            
		   $result = parent::insert($DB."person_subtract_flows", $this);
		
		if($result === false)
		{ 
			
			return false;
		}
                
		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_add;		
		$daObj->MainObjectID = $this->subtract_id ; 
		$daObj->SubObjectID = $this->row_no ; 
		$daObj->TableName = "person_subtract_flows";
		$daObj->execute();   
		
		return true  ; 
	}
	        
}

?>