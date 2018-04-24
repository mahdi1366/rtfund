<?php
//---------------------------
// programmer:	Mahdipour
// create Date:	91.06
//---------------------------

class manage_bank extends PdoDataAccess
{
	public $bank_id ;
	public $name;
	public $branch_code;
        public $type ; 
        
	function __construct($bank_id = "")
	 {
		        
	 	return;
	 }

	 static function GetAll($where)
	 {
	 	
                $query = "  select * from HRM_banks   " ; 
                
	 	return parent::runquery($query);
	 }

        
	function Add()
	{
          
            $this->bank_id = parent::GetLastID("HRM_banks","bank_id") + 1 ; 
                if($this->branch_code == 0 ||  $this->branch_code == null ) 
		$this->branch_code = PDONULL ; 
            $return = parent::insert("HRM_banks",$this);

            if($return === false)
                    return false;

            $daObj = new DataAudit();
            $daObj->ActionType = DataAudit::Action_add;
            $daObj->MainObjectID = $this->bank_id;                
            $daObj->TableName = "HRM_banks";
            $daObj->execute();

            return true;
	}
        
        function Edit() {
                        
	         if($this->branch_code == 0 ||  $this->branch_code == null ) 
		    $this->branch_code = PDONULL ; 
		 
                $result = parent::update("HRM_banks", $this,"bank_id=".$this->bank_id );
                
                if ($result === false)
			return false;	

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_update;
		$daObj->MainObjectID = $this->bank_id; 
		$daObj->TableName = "HRM_banks";
		$daObj->execute();

		return true;
	}	

	static function Remove($bid)
	{                                
                $res = parent::runquery(" select count(*) cn from HRM_staff where bank_id = ".$bid );

                if($res[0]['cn'] > 0 )
                {
                    parent::PushException("حذف این رکورد امکان پذیر نمی باشد.");
                    return false ;
                }
            
                        $result = parent::delete("HRM_banks","bank_id=:BID ",
                                                                            array(":BID" => $bid));                

                        if(!$result)
                                return false;

                        $daObj = new DataAudit();
                        $daObj->ActionType = DataAudit::Action_delete;
                        $daObj->MainObjectID = $bid;                       
                        $daObj->TableName = "HRM_banks";
                        $daObj->execute();

                        return true;
        }

}

?>