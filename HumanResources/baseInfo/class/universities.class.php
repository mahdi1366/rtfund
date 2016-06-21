<?php
//---------------------------
// programmer:	Mahdipour
// create Date:	91.05
//---------------------------

class manage_University extends PdoDataAccess
{
	public $country_id ;
	public $university_id;
	public $university_category;
        public $ptitle ; 
        public $etitle ;
        
	function __construct($university_id = "")
	 {
		        
	 	return;
	 }

	 static function GetAll($where)
	 {
	 	
                $query = " SELECT u.country_id , u.university_id , u.university_category , u.ptitle , u.etitle , c.ptitle country_title 
                                FROM universities  u inner join countries c 
                                                            on u.country_id = c.country_id 
                                                     " ; 
	 	return parent::runquery($query);
	 }

        
	function Add()
	{
            $qry = "select max(university_id) uid from universities where country_id = ". $this->country_id ; 
            $res = parent::runquery($qry) ; 
            $this->university_id = $res[0]['uid'] + 1  ;
            
                $return = parent::insert("universities",$this);

	 	if($return === false)
			return false;

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_add;
		$daObj->MainObjectID = $this->university_id;                
		$daObj->TableName = "universities";
		$daObj->execute();

		return true;
	}
        
        function Edit() {
            
                $query = "update universities set ptitle = '".$this->ptitle."' 
                                        where university_id= ".$this->university_id ." and country_id = ".$this->country_id ; 
                $result = parent::runquery($query) ; 
                
                if ($result === false)
			return false;	

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_update;
		$daObj->MainObjectID = $this->university_id;
                $daObj->SubObjectID = $this->country_id ; 
		$daObj->TableName = "universities";
		$daObj->execute();

		return true;
	}	

	static function Remove($uid , $cid  )
	{
            
                    
                $res = parent::runquery(" select count(*) cn from person_educations where university_id = ".$uid ." and country_id =".$cid );

                if($res[0]['cn'] > 0 )
                {
                    parent::PushException("حذف این رکورد به علت استفاده در اطلاعات پرسنل امکان پذیر نمی باشد.");
                    return false ;
                }
            
                        $result = parent::delete("universities","university_id=:UID and  country_id = :CID ",
                                                                            array(":UID" => $uid , 
                                                                                  ":CID" => $cid ));                

                        if(!$result)
                                return false;

                        $daObj = new DataAudit();
                        $daObj->ActionType = DataAudit::Action_delete;
                        $daObj->MainObjectID = $uid;
                        $daObj->SubObjectID = $cid;
                        $daObj->TableName = "universities";
                        $daObj->execute();

                        return true;
        }

}

?>