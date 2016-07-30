<?php
//---------------------------
// programmer:	Mahdipour
// create Date:	91.06
//---------------------------
require_once 'mission_list_items.class.php';
require_once 'pay_get_list_items.class.php';

class manage_pay_get_lists extends PdoDataAccess
{
	
	public $list_id ;
	public $list_date ;	
	public $doc_state ;
        public $list_type ;
        public $cost_center_id ; 

	function __construct()
	{
		    
                $this->DT_list_date = DataMember::CreateDMA(DataMember::DT_DATE);
	}
	
	static function GetAll($where)
	 {  
		    $query = " SELECT pgl.list_id , pgl.list_date , pgl.doc_state , pgl.list_type , 
				    pgl.cost_center_id , cc.title cost_center_title , bi.Title list_title 

				    FROM pay_get_lists pgl inner join cost_centers cc 
								on pgl.cost_center_id = cc.cost_center_id  
							inner join Basic_Info bi 
								on  bi.typeid = 47 and bi.InfoID = pgl.list_type ".$where ;
		
        	   
	 	return parent::runquery($query) ; 
	 }
	 	 
        function AddList()
	{ 	            
                $result = parent::insert("pay_get_lists", $this);
		
		if($result === false)
		{ 
			return false;
		}

		$this->list_id = parent::InsertID();

                
		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_add;		
		$daObj->MainObjectID = $this->list_id;
		$daObj->TableName = "pay_get_lists";
		$daObj->execute();   
		
		return true  ; 
	}
	
	function EditList()
	{ 
	 	$whereParams = array();
	 	$whereParams[":lid"] = $this->list_id;
                  
	 	$result = parent::update("pay_get_lists",$this," list_id=:lid", $whereParams);
      
		if(!$result)
			return false;

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_update;
		$daObj->MainObjectID = $this->list_id;
		$daObj->TableName = "pay_get_lists";
		$daObj->execute();

		return true;
	}
	
	function Remove()
	{
	
		if($this->list_type == 9 ) 
			$tblName = "mission_list_items" ; 
		else $tblName = "pay_get_list_items" ;

		$query = " select * from ".$tblName." where list_id = ".$this->list_id  ;
		$tmp = parent::runquery($query);

		$query = " select * from pay_get_lists where list_id = ".$this->list_id ." and doc_state = 3 " ;
		$tmp2 = parent::runquery($query);

		if(count($tmp) >  0 )
		{
		    parent::PushException("این لیست شامل مجموعه ای از افراد می باشد .");
		    return false ;            
		}
		else if (count($tmp2) >  0)
		{
		    parent::PushException("این لیست تایید واحد مرکزی می باشد .");
		    return false ;
		}
		else
		{
		    $result = parent::delete("pay_get_lists","list_id=?", array($this->list_id));
		}
        
		if(!$result)
			return false;

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_delete;
		$daObj->MainObjectID = $this->list_id;
		$daObj->TableName = "pay_get_lists";
		$daObj->execute();

		return true;
	}
        
}

?>