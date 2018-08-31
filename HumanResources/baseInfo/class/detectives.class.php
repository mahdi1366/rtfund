<?php
//---------------------------
// programmer:	Mahdipour
// create Date:	91.05
//---------------------------

class manage_University extends PdoDataAccess
{
	public $DetID ;
	public $detectiveName;
	public $detectiveCode;
        
	function __construct($university_id = "")
	 {
		        
	 	return;
	 }

	 static function GetAll($where)
	 {
	 	
                $query = " SELECT DetID ,detectiveCode ,detectiveName 
                                FROM HRM_Detectives 
                                                     " ; 
	 	return parent::runquery($query);
	 }

        
	function Add()
	{
         /*   $qry = "select max(DetID) uid from HRM_Detectives where country_id = ". $this->country_id ; 
            $res = parent::runquery($qry) ; 
            $this->university_id = $res[0]['uid'] + 1  ; */
            
                $return = parent::insert("HRM_Detectives",$this);

	 	if($return === false)
			return false;

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_add;
		$daObj->MainObjectID = $this->DetID;                
		$daObj->TableName = "HRM_Detectives";
		$daObj->execute();

		return true;
	}
        
        function Edit() {
           
            
                $query = "update HRM_Detectives 
                set detectiveName = '".$this->detectiveName."' , detectiveCode ='".$this->detectiveCode."' 
                                        where   DetID = ".$this->DetID ; 
                $result = parent::runquery($query) ; 
                
                if ($result === false)
			return false;	

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_update;
		$daObj->MainObjectID = $this->DetID;               
		$daObj->TableName = "HRM_Detectives";
		$daObj->execute();

		return true;
	}	

	static function Remove($uid , $cid  )
	{
            
                    
                $res = parent::runquery(" select count(*) cn from HRM_person_educations where university_id = ".$uid ." and country_id =".$cid );

                if($res[0]['cn'] > 0 )
                {
                    parent::PushException("حذف این رکورد به علت استفاده در اطلاعات پرسنل امکان پذیر نمی باشد.");
                    return false ;
                }
            
                        $result = parent::delete("HRM_universities","university_id=:UID and  country_id = :CID ",
                                                                            array(":UID" => $uid , 
                                                                                  ":CID" => $cid ));                

                        if(!$result)
                                return false;

                        $daObj = new DataAudit();
                        $daObj->ActionType = DataAudit::Action_delete;
                        $daObj->MainObjectID = $uid;
                        $daObj->SubObjectID = $cid;
                        $daObj->TableName = "HRM_universities";
                        $daObj->execute();

                        return true;
        }

}

?>