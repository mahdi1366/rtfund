<?php
//---------------------------
// programmer:	Mahdipour
// create Date:	91.05.03
//---------------------------

class manage_cost_center_exception extends PdoDataAccess
{
	public $SalaryItemTypeID ;
	public $PersonType;
	public $CostCenterID;
        public $FromDate ; 
        public $ToDate ; 

        function __construct($salary_item_type_id = "")
	 {
		 $this->DT_FromDate = DataMember::CreateDMA(DataMember::DT_DATE);
                 $this->DT_ToDate = DataMember::CreateDMA(DataMember::DT_DATE);
	 	return;
	 }

	 static function GetAll($where)
	 {	 	
                $query = " SELECT SalaryItemTypeID , PersonType , CostCenterID , cc.title cost_center_title , cc.cost_center_id , bi.Title person_type_title , sit.full_title , 
                                  cce.FromDate , cce.ToDate

                                    FROM CostCenterException cce inner join cost_centers cc
                                                                        on cce.CostCenterID = cc.cost_center_id

                                                            inner join Basic_Info bi
                                                                                                                    on cce.PersonType = bi.InfoID and bi.typeid = 16

                                                                                                        inner join salary_item_types sit

                                                                                                                    on cce.SalaryItemTypeID = sit.salary_item_type_id
                                                                                                                    
            order by CostCenterID " ; 
                
 
	 	return parent::runquery($query);
	 }

        
	function Add()
	{
            
            $query = " select * from CostCenterException where SalaryItemTypeID= ".$this->SalaryItemTypeID." and 
                                                               PersonType = ".$this->PersonType ." and CostCenterID = ". $this->CostCenterID  ;
            $res = parent::runquery($query);
            
            if(count($res) > 0 )
            {
                 parent::PushException(IS_AVAILABLE);
			return false;                
            }

        	$return = parent::insert("CostCenterException",$this);

	 	if($return === false)
			return false;

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_add;
		$daObj->MainObjectID = $this->SalaryItemTypeID;
                $daObj->SubObjectID = $this->PersonType ; 
		$daObj->TableName = "CostCenterException";
		$daObj->execute();

		return true;
	}
        
         function Edit() {
		$whereParams = array();
		$whereParams[":sid"] = $this->SalaryItemTypeID;
                $whereParams[":ccid"] = $this->CostCenterID;
                		
		$result = parent::update("CostCenterException", $this, " SalaryItemTypeID=:sid and CostCenterID =:ccid ", $whereParams);

		if (!$result)
			return false;

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_update;
		$daObj->MainObjectID = $this->SalaryItemTypeID;	
		$daObj->TableName = "CostCenterException";
		$daObj->execute();

		return true;
	}

		

	static function Remove($sid , $pt , $cid )
	{
            
                    $result = parent::delete("CostCenterException","SalaryItemTypeID=:SID and  PersonType = :PT and CostCenterID = :CC ",
                                                                            array(":SID" => $sid , 
                                                                                  ":PT" => $pt ,
                                                                                  ":CC" => $cid ));                

                        if(!$result)
                                return false;

                        $daObj = new DataAudit();
                        $daObj->ActionType = DataAudit::Action_delete;
                        $daObj->MainObjectID = $sid;
                        $daObj->SubObjectID = $cid;
                        $daObj->TableName = "CostCenterException";
                        $daObj->execute();

                        return true;
        }

}

?>