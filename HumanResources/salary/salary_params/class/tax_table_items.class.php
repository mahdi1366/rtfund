<?php
//---------------------------
// programmer:	Mahdipour
// create Date:	90.09
//---------------------------

class manage_Tax_Table_Item extends PdoDataAccess
{
	
	public $tax_table_id;
	public $row_no;
	public $from_value;
	public $to_value;
	public $coeficient;

	function __construct( $tax_table_id="" , $row_no="" )
	{
		if($tax_table_id != "" && $row_no != "" )
		{
			parent::FillObject($this, "select * from tax_table_items where tax_table_id=:tid and row_no=:rid ", array("tid"=>$tax_table_id , "rid"=>$row_no ));
		}
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
	
	function GetAll($tax_table_id, $where="", $whereParam=array())
	{
		$query = "select * from HRM_tax_table_items ";
		
        $query .= ( !empty($where) ) ? $where : '' ;

		return PdoDataAccess::runquery($query ,$whereParam);
	}

	function AddTaxItem()
	{ 
		
		$this->row_no = parent::GetLastID("HRM_tax_table_items", "row_no"," tax_table_id =".$this->tax_table_id) + 1 ;

		$result = parent::insert("HRM_tax_table_items", $this);

		if($result === false)
		{
			return false;
		}		

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_add;		
		$daObj->MainObjectID = $this->tax_table_id;
		$daObj->SubObjectID = $this->row_no ;
		$daObj->TableName = "HRM_tax_table_items" ;
		$daObj->execute();   
		
		return true;
	}
	
	function EditTaxItem($tax_table_id,$row_no)
	{
		                   
        $result = parent::update("HRM_tax_table_items", $this,
                                 "tax_table_id=:tid and row_no=:rid ",
                                 array(":tid" => $this->tax_table_id ,
									   ":rid" => $this->row_no ));
		if($result === false)
			return false;

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_update;
		$daObj->MainObjectID = $this->tax_table_id;
		$daObj->SubObjectID = $this->row_no ; 
		$daObj->TableName = "HRM_tax_table_items";
		$daObj->execute();

		return true;
	}
	
	function RemoveTaxItem($tax_table_id, $row_no)
	{
	 			
        $result = parent::delete("tax_table_items", "tax_table_id=:tid and row_no=:rid",
                                                   array(":tid" => $this->tax_table_id , ":rid" => $this->row_no ));
		if($result === false)
			return false;

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_add;
		$daObj->MainObjectID = $this->tax_table_id;
		$daObj->SubObjectID = $this->row_no ; 
		$daObj->TableName = "tax_table_items";
		$daObj->execute();

		return true;
	}
	
	
}

?>