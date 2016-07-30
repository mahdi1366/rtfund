<?php
//---------------------------
// programmer:	Mahdipour
// create Date:	92.08
//---------------------------
require_once 'person_subtract_flows.class.php';


class manage_person_subtracts extends PdoDataAccess
{
	
	public $subtract_id ;
	public $staff_id ;	
	public $subtract_type ;
	public $bank_id ;
	public $first_value ; 
	public $instalment ; 
	public $remainder ; 
	public $start_date ; 
	public $end_date ; 
	public $comments ; 
	public $salary_item_type_id ; 	
	public $loan_no ; 
	public $flow_date ; 
	public $flow_time ;
	public $subtract_status ;
	public $contract_no ;
	
	function __construct()
	{
		    
                $this->DT_start_date = DataMember::CreateDMA(DataMember::DT_DATE);
				$this->DT_end_date = DataMember::CreateDMA(DataMember::DT_DATE);
				$this->DT_flow_date = DataMember::CreateDMA(DataMember::DT_DATE);				
	}
	
	function AddSub($DB="")
	{ 	            
				 
		   $result = parent::insert($DB."person_subtracts", $this);
				 
		if($result === false)
		{   
			return false;
		}

		$this->subtract_id = parent::InsertID();

                
		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_add;		
		$daObj->MainObjectID = $this->subtract_id;
		$daObj->TableName = "person_subtracts";
		$daObj->execute();   
		
		return true  ; 
	}
	
	
}

?>