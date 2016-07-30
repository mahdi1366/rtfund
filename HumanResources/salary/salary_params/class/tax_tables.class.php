<?php
//---------------------------
// programmer:	Mahdipour
// create Date:	90.09
//---------------------------

class manage_Tax_Table extends PdoDataAccess
{
	
	public $tax_table_id;
	public $from_date;
	public $to_date;
	public $tax_table_type_id;

	function __construct( $tax_table_id="", $tax_table_type_id="" )
	{
		if($tax_table_id != "")
		{
			parent::FillObject($this, "select * from tax_tables where tax_table_id=:tid", array("tid"=>$tax_table_id));
		}
		else if($tax_table_type_id != "")
		{
			$query = "select * from tax_tables
                            where tax_table_type_id = :ttid ";			

			$whereParam = array();
			$whereParam[":ttid"] = $tax_table_type_id;
			
			parent::FillObject($this, $query, $whereParam);
		}    
    
        $this->DT_from_date = DataMember::CreateDMA(DataMember::DT_DATE);
		$this->DT_to_date = DataMember::CreateDMA(DataMember::DT_DATE);
	}

	private function onBeforeDelete()
	{
        $res = parent::runquery(" select * from tax_table_items
													where tax_table_id = ".$this->tax_table_id );

        if(count($res) > 0 )
        {
            parent::PushException("این رکورد دارای اطلاعات وابسته می باشد.");
			return false;
        }		

        return true ;
    }
	
	function GetAll($tax_table_type_id, $where="", $whereParam=array())
	{
		$query = "select * from HRM_tax_tables ";
		
        $query .= ( !empty($where) ) ? $where : '' ;

		return PdoDataAccess::runquery($query ,$whereParam);
	}

	function AddTax()
	{ 
		if(!$this->date_overlap())
			return false;
		
		$result = parent::insert("HRM_tax_tables", $this);

		if($result === false)
		{
			return false;
		}

		$this->tax_table_id = parent::InsertID();

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_add;		
		$daObj->MainObjectID = $this->tax_table_id;
		$daObj->TableName = "HRM_tax_tables";
		$daObj->execute();   
		
		return true;
	}
	
	function EditTax($tax_table_id)
	{
		        
        if(!$this->date_overlap())
			return false;
            
        $result = parent::update("HRM_tax_tables", $this,
                                 "tax_table_id=:tid ",
                                 array(":tid" => $this->tax_table_id ));
		if($result === false)
			return false;

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_update;
		$daObj->MainObjectID = $this->tax_table_id;
		$daObj->TableName = "HRM_tax_tables";
		$daObj->execute();

		return true;
	}
	
	function RemoveTax()
	{
	 	
        if(!$this->onBeforeDelete())
			return false;
		
        $result = parent::delete("tax_tables", "tax_table_id=:tid ",
                                                   array(":tid" => $this->tax_table_id ));
		if($result === false)
			return false;

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_add;
		$daObj->MainObjectID = $this->tax_table_id;
		$daObj->TableName = "tax_tables";
		$daObj->execute();

		return true;
	}
	
	 private function date_overlap()
	 {
		 if($this->tax_table_id == null )
            $W = "" ;
          else
            $W = " AND  tax_table_id != ".$this->tax_table_id ;


        $query = "	select count(*)
							from tax_tables
									where
											((from_date <=:fdate AND to_date>=:fdate) OR (from_date>=:fdate AND from_date <=:tdate)) AND
											  tax_table_type_id=:ttid ".$W ;
	 	
		$whereParam = array();
		$whereParam[":fdate"] = DateModules::shamsi_to_miladi($this->from_date);
		$whereParam[":tdate"] = DateModules::shamsi_to_miladi($this->to_date);
		$whereParam[":ttid"] = $this->tax_table_type_id;	

       
            $temp = PdoDataAccess::runquery($query, $whereParam);

			
		if($temp[0][0] != 0)
		{
			parent::PushException(ER_DATE_RANGE_OVERLAP);
			return false;
		}
		return true ;
	 }
}

?>