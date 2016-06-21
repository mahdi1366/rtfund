<?php
//---------------------------
// programmer:	Mahdipour
// create Date:	92.10
//---------------------------

class manage_priority extends PdoDataAccess
{
	public $PriorityID ;
	public $PriorityTitle ;
	        
	function __construct($bank_id = "")
	 {
		        
	 	return;
	 }

	 static function GetAll($where)
	 {
	 	
		$query = "  select * from priority   ".$where ; 
                
	 	return parent::runquery($query);
	 }

        
	function Add()
	{
          		
            $return = parent::insert("priority",$this);

            if($return === false)
                    return false;

            $daObj = new DataAudit();
            $daObj->ActionType = DataAudit::Action_add;
            $daObj->MainObjectID = $this->PriorityID;                
            $daObj->TableName = "priority";
            $daObj->execute();

            return true;
	}
        
	function Edit() {
            
			$result = parent::update("priority", $this,"PriorityID=".$this->PriorityID );

			if ($result === false)
			return false;	

			$daObj = new DataAudit();
			$daObj->ActionType = DataAudit::Action_update;
			$daObj->MainObjectID = $this->PriorityID; 
			$daObj->TableName = "priority";
			$daObj->execute();

			return true;
	}	

	static function Remove($pid)
	{     
		
                $res = parent::runquery(" select count(*) cn from SubtractItemInfo where arrangement = ".$pid );

                if($res[0]['cn'] > 0 )
                {
                    parent::PushException("حذف این رکورد امکان پذیر نمی باشد.");
                    return false ;
                }
            
				$result = parent::delete("priority","PriorityID=:PID ",
																	array(":PID" => $pid));                

				if(!$result)
						return false;

				$daObj = new DataAudit();
				$daObj->ActionType = DataAudit::Action_delete;
				$daObj->MainObjectID = $pid;                       
				$daObj->TableName = "priority";
				$daObj->execute();

				return true;
        }

}

?>