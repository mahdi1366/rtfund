<?php
//---------------------------
// programmer:	Mahdipour
// create Date:	91.01.22
//---------------------------
require_once inc_manage_unit ;
class Retired_Base_Salary extends PdoDataAccess
{
	
	public $salary_94;
    public $ledger_number;

	function __construct()
	 {		
	 	return;
	 }

	 static function GetAll($where)
	 {
	 	$query = " select el.* , bi.title person_title
                            from evaluation_lists el left join Basic_Info bi on el.person_type = bi.InfoID and bi.TypeID = 16
                                                          ".$where;
        
        $tmp = parent::runquery($query);

        for($i=0 ; $i < count($tmp) ; $i++ ){

				$tmp[$i]['unit_full_title'] = manage_units::get_full_title($tmp[$i]['ouid']);

			}

	 	return $tmp ;
	 }

     static function GetAllMembers($where)
	 {	
		$where .= " order by p.plname , p.pfname " ; 
	 	$query = " select eli.* ,p.pfname , p.plname 
                                                    from evaluation_list_items eli
                                                            inner join staff s on eli.staff_id = s.staff_id
                                                                inner join persons p on s.personid = p.personid  ".$where;

        $tmp = parent::runquery($query);

	 	return $tmp ;
	 }

	

	function AddList()
	{

        $lastid = parent::GetLastID("evaluation_lists", "list_id");
	 	$this->list_id = ($lastid + 1);
	 	$return = parent::insert("evaluation_lists",$this);

	 	if($return === false)
			return false;

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_add;
		$daObj->MainObjectID = $this->list_id;
		$daObj->TableName = "evaluation_lists";
		$daObj->execute();

		return true;
	}

	function EditList()
	{ 
	 	$whereParams = array();
	 	$whereParams[":lid"] = $this->list_id;
                  
	 	$result = parent::update("evaluation_lists",$this," list_id=:lid", $whereParams);
      
        if(!$result)
			return false;

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_update;
		$daObj->MainObjectID = $this->list_id;
		$daObj->TableName = "evaluation_lists";
		$daObj->execute();

		return true;
	}

	

	function Remove()
	{

        $query = " select * from evaluation_list_items where list_id = ".$this->list_id  ;
        $tmp = parent::runquery($query);

        $query = " select * from evaluation_lists where list_id = ".$this->list_id ." and doc_state = 3 " ;
        $tmp2 = parent::runquery($query);

        if(count($tmp) >  0 )
        {
            parent::PushException(" این لیست ارزشیابی شامل مجموعه ای از افراد می باشد .");
            return false ;            
        }
        else if (count($tmp2) >  0)
        {
             parent::PushException("این لیست تایید واحد مرکزی می باشد .");
             return false ;
        }
        else
        {
            $result = parent::delete("evaluation_lists","list_id=?", array($this->list_id));
        }
        
		if(!$result)
			return false;

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_delete;
		$daObj->MainObjectID = $this->list_id;
		$daObj->TableName = "evaluation_lists";
		$daObj->execute();

		return true;
	}

}

?>