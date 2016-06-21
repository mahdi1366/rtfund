<?php
//---------------------------
// programmer:	Jafarkhani
// create Date:	88.08
//---------------------------

class manage_writ_subType extends PdoDataAccess
{
	public $person_type;
	public $writ_type_id;
	public $writ_subtype_id;
	public $title;
	public $description;
	public $emp_state;
	public $emp_mode;
	public $worktime_type;
	public $edit_fields;
	public $time_limited;
	public $req_staff_signature;
	public $automatic;
	public $salary_pay_proc;
	public $items_effect;
	public $post_effect;
	public $annual_effect;
	public $remember_distance;
	public $remember_message;
	public $print_title;
	public $comments;
	public $show_in_summary_doc;
	
	function __construct($person_type, $writ_type_id,$writ_subtype_id="" )
	{
		if($person_type != "" && $writ_type_id != "" && $writ_subtype_id != "")
		{
			$query = "select * from HRM_writ_subtypes 
                            where person_type = :ptype and
                                  writ_type_id = :wTypeId and
                                  writ_subtype_id = :wSubTypeId";
			
			$whereParam = array(":ptype" => $person_type , 
			                    ":wTypeId" => $writ_type_id ,
			                    ":wSubTypeId" => $writ_subtype_id );
						
			PdoDataAccess::FillObject($this, $query, $whereParam);
		}
	}

    function AddWST()
	{ 
	 	$wsid = PdoDataAccess::GetLastID("writ_subtypes", "writ_subtype_id" , 
                                         "person_type = :PT and writ_type_id = :WTI " ,
                                          array(":PT" => $this->person_type , ":WTI" => $this->writ_type_id ) );
        $wsid ++ ;
        $this->writ_subtype_id = $wsid ;

        $return = parent::insert("writ_subtypes", $this);


	 	if($return === false)
			return false;

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_add;
		$daObj->MainObjectID = $this->person_type."-".$this->writ_type_id."-".$this->writ_subtype_id;
		$daObj->TableName = "writ_subtypes";
		$daObj->execute();

		return true;
	}

    function EditWST()
	{
	 	parent::update("writ_subtypes", $this, " person_type = :PT and writ_type_id = :WTI and writ_subtype_id = :WSTID ", 
                       array(":PT" => $this->person_type , ":WTI" => $this->writ_type_id , ":WSTID" => $this->writ_subtype_id ));

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_update;
		$daObj->MainObjectID = $this->person_type."-".$this->writ_type_id."-".$this->writ_subtype_id;
		$daObj->TableName = "writ_subtypes";
		$daObj->execute();

	 	return true;
	}

    static function DeleteWST($id)
    {
        $arr = explode('-',$id);
       
        PdoDataAccess::runquery("delete from writ_subtypes where person_type=" .$arr[3]." and writ_type_id=".$arr[4]." and writ_subtype_id=".$arr[5] );

        $daObj = new DataAudit();
        $daObj->ActionType = DataAudit::Action_delete;
        $daObj->MainObjectID = $_POST["id"];
        $daObj->TableName = "writ_subtypes";
        $daObj->execute();

    }
	 
	
}

?>