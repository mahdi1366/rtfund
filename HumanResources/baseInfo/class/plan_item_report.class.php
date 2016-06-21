<?php
//---------------------------
// programmer:	Mahdipour
// create Date:	92.03
//---------------------------

class manage_plan_item_report extends PdoDataAccess
{
	public $PlanItemID ;
	public $PlanItemTitle;
	public $PayYear;
	public $PayMonth ;
	public $PayValue ;
	public $RelatedItem ; 
    public $CostCenterID ; 
	public $PersonType ; 
	
	function __construct($PlanItemReportID = "")
	 {		        
	 	return;
	 }

	 static function GetAll($where)
	 {
	 	
		$query = " SELECT   pir.PlanItemID, pir.PlanItemTitle , pir.PayYear , pir.PayMonth , 
							pir.PayValue , pir.RelatedItem , pir.CostCenterID , pir.PersonType , bi.infoID , bi.Title
							
						FROM PlanItemReport pir left join Basic_Info bi 
						                             on pir.RelatedItem = bi.InfoID and bi.typeid = 49 " ; 

	 	return parent::runquery($query);
		
		return 0 ; 
		
	 }

        
	function Add()
	{             
          
		$return = parent::insert("PlanItemReport",$this);

	 	if($return === false)
			return false;

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_add;
		$daObj->MainObjectID = $this->PlanItemID;                
		$daObj->TableName = "PlanItemReport";
		$daObj->execute();

		return true;
	}
        
        function Edit() {
			$whereParams = array();
			$whereParams[":pir"] = $this->PlanItemID;

			$result = parent::update("PlanItemReport", $this, " PlanItemID=:pir ", $whereParams);
			
			if (!$result)
				return false;

			$daObj = new DataAudit();
			$daObj->ActionType = DataAudit::Action_update;
			$daObj->MainObjectID = $this->PlanItemID;	
			$daObj->TableName = "PlanItemReport";
			$daObj->execute();

			return true;
		}		

	static function Remove($pid)
	{
            
			$result = parent::delete("PlanItemReport","PlanItemID=:PID ",
																array(":PID" => $pid ));                

			if(!$result)
					return false;

			$daObj = new DataAudit();
			$daObj->ActionType = DataAudit::Action_delete;
			$daObj->MainObjectID = $pid;                        
			$daObj->TableName = "PlanItemReport";
			$daObj->execute();

			return true;
	}
        
        function change_state()
        {
            $whereParams = array();
            $whereParams[":py"] = $this->PayYear;
            $whereParams[":pm"] = $this->PayMonth;           
                        
             if($this->PersonType == 102 )
                $pt = " in ( 1,2,3) " ; 
             else if ($this->PersonType == 100 )
                  $pt = " in ( 1,2,3,5,100 ) " ; 
            else  
                $pt = $this->PersonType ;            
            
            $query = " update SalaryItemReport
                                  set state = ".$this->state."
                                                 where PayYear = :py and PayMonth = :pm and PersonType $pt  " ; 
            
            $result = parent::runquery($query, $whereParams) ; 
          
	 	if($result === false )
	 		return false;

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_update;
		$daObj->MainObjectID = $this->PayMonth;
                $daObj->SubObjectID = $this->state;
		$daObj->TableName = "SalaryItemReport";
		$daObj->execute();

	 	return true;
            
        }

}

?>