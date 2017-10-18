<?php
//---------------------------
// programmer:	Mahdipour
// create Date:	90.09
//---------------------------

class manage_tax_table_types extends PdoDataAccess
{
	
	public $tax_table_type_id;
	public $title;
	public $person_type;
	
	function __construct($tax_table_type_id="" ,$person_type = "")
	{
		 $query = " SELECT   *
	                	FROM tax_table_types
	                		WHERE  tax_table_type_id = :tid and person_type =:pt
	                		 ";

		 $whereParam = array(":tid" => $tax_table_type_id, ":pt" => $person_type);

		 $temp = parent::runquery($query, $whereParam);
         parent::FillObject($this, $query, $whereParam );
	}

	private function onBeforeDelete()
	{
        $res = parent::runquery(" select * from HRM_tax_tables
													where tax_table_type_id = ".$this->tax_table_type_id );

        if(count($res) > 0 )
        {
            parent::PushException(TAX_CAN_NOT_DELETE);
			return false;

        }

		$resstaff = parent::runquery(" select * from HRM_staff_tax_history
													where tax_table_type_id = ".$this->tax_table_type_id );

        if(count($resstaff) > 0 )
        {
            parent::PushException(STAFF_TAX_CAN_NOT_DELETE);
			return false;

        }
		
        return true ;
    }

		
	function GetAll($where)
	{
		$query = " select * , 'قراردادی' person_title from HRM_tax_table_types  ".$where;
      		  
		return PdoDataAccess::runquery($query);
	}

	function AddTax()
	{		       
		
        $tax_table_type_id = parent::GetLastID("HRM_tax_table_types", "tax_table_type_id" );
        $tax_table_type_id ++ ;
        $this->tax_table_type_id =  $tax_table_type_id ;
       
		$result = parent::insert("HRM_tax_table_types", $this);
       
        if($result === false)
			return false;
            
		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_add;		
		$daObj->MainObjectID = $this->tax_table_type_id ;
		$daObj->TableName = "HRM_tax_table_types";
		$daObj->execute();
               
		return true;
	}
	
	function EditTax($tax_table_type_id)
	{
		                   
        $result = parent::update("HRM_tax_table_types", $this,
                                 "tax_table_type_id=:ti ",
                                 array(":ti" => $this->tax_table_type_id ));
		if($result === false)
			return false;

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_update;
		$daObj->MainObjectID = $this->tax_table_type_id ;
		$daObj->TableName = "HRM_tax_table_types";
		$daObj->execute();

		return true;
	}
	
	function RemoveTax($tax_table_type_id , $person_type)
	{    
	 
        if(!$this->onBeforeDelete())
			return false;
            
        $result = parent::delete("HRM_tax_table_types", "tax_table_type_id=:tid and person_type = :pt ",
                                  array(":tid" => $this->tax_table_type_id , ":pt" => $this->person_type ));

                           
		if($result === false)
			return false;

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_delete;		
		$daObj->MainObjectID = $this->tax_table_type_id;
		$daObj->TableName = "tax_table_types";
		$daObj->execute();

		return true;
	}
	
}

?>