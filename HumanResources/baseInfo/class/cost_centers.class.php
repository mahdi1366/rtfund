<?php
//---------------------------
// programmer:	Mahdipour
// create Date:	91.05.12
//---------------------------

class manage_cost_centers extends PdoDataAccess
{
	public $cost_center_id ;
	public $title;
	public $daily_work_place_no;
        public $detective_name ; 
        public $employer_name ;
        public $detective_address ; 
        public $collective_security_branch ;
        public $description ; 

	function __construct($cost_center_id = "")
	 {
		        
	 	return;
	 }

	 static function GetAll($where)
	 {
	 	
                $query = " SELECT cost_center_id ,title , daily_work_place_no , detective_name , employer_name , detective_address  , collective_security_branch  , description 

                                    FROM cost_centers  " ; 

	 	return parent::runquery($query);
	 }

        
	function Add()
	{		
	 	$return = parent::insert("cost_centers",$this);

	 	if($return === false)
			return false;
		
		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_add;
		$daObj->MainObjectID = $this->cost_center_id;
		$daObj->TableName = "cost_centers";
		$daObj->execute();
		return true;
	}
        
        function Edit() {
		$whereParams = array();
		$whereParams[":ccid"] = $this->cost_center_id;
		
                $result = parent::update("cost_centers", $this, "cost_center_id=:ccid",$whereParams) ; 
		
                if ($result === false)
			return false;	

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_update;
		$daObj->MainObjectID = $this->cost_center_id;
		$daObj->TableName = "cost_centers";
		$daObj->execute();

		return true;
	}	

	static function Remove($CostID)
	{
		$query = " select count(*) cn 
				from writs 
				    where  cost_center_id = ".$CostID ; 
		$res = parent::runquery($query) ;
		if($res[0]['cn'] > 0 )
		{
		     parent::PushException("این مرکز هزینه در احکام ثبت گردیده است لذا حذف امکان پذیر نمی باشد.");
		    return false ;
		}
		$result = parent::delete("cost_centers", "cost_center_id=:CID ", array(":CID" => $CostID));
		
		if($result === false)
			return false;

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_delete;
		$daObj->MainObjectID = $CostID;
		$daObj->TableName = "cost_centers";
		$daObj->execute();

		return true;
	}

}

?>