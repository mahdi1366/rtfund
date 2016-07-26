<?php
//---------------------------
// programmer:	Mahdipour
// create Date:	91.06
//---------------------------

class manage_pay_get_list_items extends PdoDataAccess
{
	
	public $list_id ;
	public $list_row_no ;
	public $staff_id ;	
        public $salary_item_type_id; 
        public $initial_amount ;
        public $approved_amount ;
        public $value ;
        public $comments ; 

	function __construct()
	{
		    
                
	}
                
        function Add()
	{ 				
		
		$masterID = parent::GetLastID("pay_get_list_items", "list_row_no", "list_id=".$this->list_id )  ; 
		$this->list_row_no = $masterID + 1 ; 
		$result = parent::insert("pay_get_list_items", $this);                       
                                
		if($result === false)
		{
			return false;
		}
		
		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_add;		
		$daObj->MainObjectID = $this->list_id;
                $daObj->SubObjectID = $this->list_row_no ; 
		$daObj->TableName = "pay_get_list_items";
		$daObj->execute();   		            
                
		return true;
	}
	
	static function GetAllMembers($where)
	 {
	 	$query = " select pgli.* , p.pfname , p.plname  
		                    from pay_get_list_items pgli inner join staff s
									on pgli.staff_id = s.staff_id 
								 inner join persons p on s.personid = p.personid 
								 ".$where ;   
			
	 	return parent::runquery($query) ;
	 }
	 
	 static function SelectListOfPrn( $CostID )
	 {

	    $whereParams = array();
	    $whereParams[":cid"] = $CostID;
	    
	    $query = " select s.staff_id , s.person_type 
				from staff s inner join writs w 
		                                 on s.staff_id = w.staff_id and 
						    s.last_writ_id = w.writ_id and s.last_writ_ver and w.writ_ver 
						where w.emp_mode not in (  ".EMP_MODE_LEAVE_WITHOUT_SALARY.",".EMP_MODE_CONVEYANCE.",
									   ".EMP_MODE_TEMPORARY_BREAK.",".EMP_MODE_PERMANENT_BREAK.",
									   ".EMP_MODE_BREAKAWAY.','.EMP_MODE_RUSTICATION.",
									   ".EMP_MODE_RETIRE.','.EMP_MODE_RE_BUY.",
									   ".EMP_MODE_WITHOUT_SALARY." ) and w.cost_center_id = :cid " ;
	    $tmp = parent::runquery($query,$whereParams);
       
	    return $tmp ;
	 }
	 
	 function Edit()
	{
	    $whereParams = array();
	    $whereParams[":lid"] = $this->list_id;
	    $whereParams[":sid"] = $this->staff_id;
	    $whereParams[":lrn"] = $this->list_row_no ; 

	    $result = parent::update("pay_get_list_items",$this," list_id=:lid and staff_id =:sid and list_row_no =:lrn ", $whereParams);

	    if(!$result)
			return false;

	    $daObj = new DataAudit();
	    $daObj->ActionType = DataAudit::Action_update;
	    $daObj->MainObjectID = $this->list_id;
	    $daObj->SubObjectID = $this->list_row_no ;
	    $daObj->TableName = "pay_get_list_items";
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
		if($All=="true")
		    $result = parent::delete("pay_get_list_items","list_id=?", array($this->list_id));

		else 
		    $result = parent::delete("pay_get_list_items","list_id=? and list_row_no =?",array($this->list_id,$this->list_row_no));


	    }

		if(!$result)
			return false;

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_delete;
		$daObj->MainObjectID = $this->list_id;
		$daObj->SubObjectID = $this->list_row_no;
		$daObj->TableName = "pay_get_list_items";
		$daObj->execute();

		return true;
	}     
 
}

?>