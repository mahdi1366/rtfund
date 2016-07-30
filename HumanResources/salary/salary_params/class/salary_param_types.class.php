<?php
//---------------------------
// programmer:	Mahdipour
// create Date:	90.02
//---------------------------

class manage_salary_param_types extends PdoDataAccess
{
	function SearchSalaryParamType($where="", $whereParam=array())
	{
		return manage_salary_params::select($where, $whereParam);
	}

	public $param_type;
	public $title;
	public $dim1_id;
	public $dim2_id;
	public $dim3_id;
	public $dim4_id;
	public $person_type;
	
	function __construct($param_type="" ,$person_type = "")
	{
		 $query = " SELECT   *
	                	FROM salary_param_types
	                		WHERE  param_type = :pid and person_type =:pt
	                		 ";

		 $whereParam = array(":pid" => $param_type, ":pt" => $person_type);

		 $temp = parent::runquery($query, $whereParam);
         parent::FillObject($this, $query, $whereParam );
	}

    private function onBeforeDelete()
	{
        $res = parent::runquery(" select * from salary_params where param_type = ".$this->param_type." and person_type = ".$this->person_type );

        if(count($res) > 0 )
        {
            parent::PushException(PARAM_CAN_NOT_DELETE);
			return false;

        }
        return true ; 
    }
	
	function GetAll()
	{                
                
		$query = " select *, 'قراردادی' person_type_title 
						from HRM_salary_param_types 
							where person_type in(3) " ;
     
		return PdoDataAccess::runquery($query);
	}

	function AddParam()
	{		       	
		
        $param_type = parent::GetLastID("HRM_salary_param_types", "param_type" ,
                                               "person_type = :PT  " ,
                                                array(":PT" => $this->person_type ) );
        $param_type ++ ;
        $this->param_type = $param_type ;
        
		$result = parent::insert("HRM_salary_param_types", $this);

        if($result === false)
			return false;
            
		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_add;		
		$daObj->MainObjectID = $this->param_type ;
        $daObj->SubObjectID = $this->person_type ;
		$daObj->TableName = "HRM_salary_param_types";
		$daObj->execute();
               
		return true;
	}
	
	function EditParam($param_id)
	{
		                   
        $result = parent::update("HRM_salary_param_types", $this,
                                 "param_type=:ptp and person_type =:pt ",
                                 array(":ptp" => $this->param_type ,
                                       ":pt" => $this->person_type ));
		if($result === false)
			return false;

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_update;
		$daObj->MainObjectID = $this->param_type ;
        $daObj->SubObjectID = $this->person_type ;
		$daObj->TableName = "HRM_salary_param_types";
		$daObj->execute();

		return true;
	}
	
	function RemoveParam($param_type , $person_type)
	{
        $obj = new manage_salary_param_types($param_type ,$person_type );
        
        if(!$obj->onBeforeDelete())
			return false;
            
        $result = parent::delete("salary_param_types", "param_type=:pid and person_type = :pt ",
                                  array(":pid" => $this->param_type , ":pt" => $this->person_type ));

                            
		if($result === false)
			return false;

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_delete;
		$daObj->RelatedPersonType = DataAudit::PersonType_staff;
		$daObj->MainObjectID = $this->param_type;
		$daObj->TableName = "salary_param_types";
		$daObj->execute();

		return true;
	}
	
	private static function LastID()
	{
	 	return PdoDataAccess::GetLastID("salary_params", "param_id");
	}

	 private function date_overlap()
	 {
		$query = "select count(*) from salary_params where 
			((from_date<=:fdate AND to_date>=:fdate) OR (from_date>=:fdate AND from_date <=:tdate)) 
			AND person_type=:hr AND param_type=:ptype";
	 	
		$whereParam = array();
		$whereParam[":fdate"] = $this->from_date;
		$whereParam[":tdate"] = $this->to_date;
		$whereParam[":hr"] = $this->person_type;
		$whereParam[":ptype"] = $this->param_type;
		
		if(isset($this->param_id))
		{
			$query .= " AND param_id<>:pid";
			$whereParam[":pid"] = $this->param_id;
		}
		
		if(isset($this->dim1_id))
		{
			$query .= " AND dim1_id = :d1id";
			$whereParam[":d1id"] = $this->dim1_id;
		}
		if(isset($this->dim2_id))
		{
			$query .= " AND dim2_id = :d2id";
			$whereParam[":d2id"] = $this->dim2_id;
		}
		if(isset($this->dim3_id))
		{
			$query .= " AND dim3_id = :d3id";
			$whereParam[":d3id"] = $this->dim3_id;
		}
		
		$temp = PdoDataAccess::runquery($query, $whereParam);
			
		if($temp[0][0] != 0)
		{
			parent::PushException(ER_DATE_RANGE_OVERLAP);
			return true;
		}
		return false;
	 }

   

}

?>